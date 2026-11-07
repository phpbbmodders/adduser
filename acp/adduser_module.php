<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser\acp;

/**
 * Add User ACP module
 *
 * Mode "main" creates one account from a form, mode "csv" creates many from
 * an uploaded CSV file. Both create every account through create_account().
 */
class adduser_module
{
	/** @var int Most data rows one CSV upload may hold */
	const CSV_MAX_ROWS = 50;

	/** @var int Largest CSV file accepted, in bytes */
	const CSV_MAX_BYTES = 1048576;

	/** @var string */
	public $u_action;

	/** @var string */
	public $page_title;

	/** @var string */
	public $tpl_name;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $phpbb_container;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $phpbb_admin_path;

	/** @var string */
	protected $php_ext;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var int[] Special group ids by name, cached by get_special_group_id() */
	protected $special_group_ids = [];

	public function main($id, $mode)
	{
		global $config, $db, $request, $template, $user, $phpbb_root_path, $phpEx, $phpbb_container, $phpbb_admin_path;

		$this->config = $config;
		$this->log = $phpbb_container->get('log');
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->language = $phpbb_container->get('language');
		$this->phpbb_container = $phpbb_container;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpbb_admin_path = $phpbb_admin_path;
		$this->php_ext = $phpEx;

		// Include files needed to add a user
		if (!function_exists('user_add'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}

		// Include lang files
		$this->language->add_lang(['posting', 'ucp', 'acp/users', 'acp/groups']);

		if ($mode === 'csv')
		{
			$this->csv_page();
			return;
		}

		$admin_activate = ($request->variable('activate', 0)) ? (($this->config['require_activation'] == USER_ACTIVATION_ADMIN) ? true : false) : false;
		$group_selected = $request->variable('group', 0);
		$default_group = $this->read_default_group_choice();
		// Newly registered users as the default group only works for a new user
		$new_user = ($default_group === 'newly_registered') ? 1 : $request->variable('new_user', 0);

		$this->page_title = $this->language->lang('ACP_ADD_USER');

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'acp_adduser';

		// Add custom profile fields
		$cp = $phpbb_container->get('profilefields.manager');

		// Set empty error strings
		$error = $cp_data = [];

		// Define the name of the form for use as a form key
		add_form_key('acp_adduser');

		// New accounts start out in the board's timezone
		$timezone = $this->config['board_timezone'];

		$data = [
			'username'			=> $request->variable('username', '', true),
			'new_password'		=> $request->variable('new_password', '', true),
			'password_confirm'	=> $request->variable('password_confirm', '', true),
			'email'				=> strtolower($request->variable('email', '')),
			'lang'				=> basename($request->variable('lang', $this->user->lang_name)),
			'tz'				=> $request->variable('tz', $timezone),
			'group'				=> $request->variable('group', 0),
		];

		// The lang being chosen must have the extension's language files. If it doesn't,
		// errors will present themselves due to no email template found.
		if (!$this->extension_has_language($data['lang']))
		{
			trigger_error(sprintf($this->language->lang('ADDUSER_DIR_NOT_EXIST'), $data['lang'], $data['lang']), E_USER_WARNING);
		}

		if ($this->config['allow_birthdays'])
		{
			$data['bday_day'] = $data['bday_month'] = $data['bday_year'] = 0;

			$data['bday_day'] = $request->variable('bday_day', $data['bday_day']);
			$data['bday_month'] = $request->variable('bday_month', $data['bday_month']);
			$data['bday_year'] = $request->variable('bday_year', $data['bday_year']);
			$data['user_birthday'] = sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);
		}

		// If form is submitted
		if ($request->is_set_post('submit'))
		{
			// Test if form key is valid
			if (!check_form_key('acp_adduser'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// A blank password is generated and the user sets their own through the
			// emailed link. Without board email nothing reaches the user, so a
			// generated password would leave the account unusable.
			$password_missing = false;

			if (empty($data['new_password']) && empty($data['password_confirm']))
			{
				if ($this->config['email_enable'])
				{
					$data['new_password'] = $data['password_confirm'] = $this->generate_password();
				}
				else
				{
					$password_missing = true;
				}
			}

			// Validate entries
			$validate_array = $this->get_validate_array();
			$validate_array['password_confirm'] = ['string', false, $this->config['min_pass_chars'], $this->config['max_pass_chars']];

			if ($password_missing)
			{
				unset($validate_array['new_password'], $validate_array['password_confirm']);
			}

			$error = validate_data($data, $validate_array);

			if ($password_missing)
			{
				$error[] = 'ADDUSER_PASSWORD_REQUIRED';
			}

			// Validate custom profile fields
			$cp->submit_cp_field('profile', $this->user->get_iso_lang_id(), $cp_data, $error);

			if ($data['new_password'] != $data['password_confirm'])
			{
				$error[] = $this->language->lang('NEW_PASSWORD_ERROR');
			}

			// Only the groups offered in the selector may be posted
			if ($data['group'] && !isset($this->get_allowed_groups()[$data['group']]))
			{
				$error[] = 'ADDUSER_GROUP_NOT_ALLOWED';
			}

			$default_group_error = $this->check_default_group_choice($default_group, $data['group']);
			if ($default_group_error !== '')
			{
				$error[] = $default_group_error;
			}

			// Replace "error" strings with their real, localised form
			$error = array_map([$this->language, 'lang'], $error);

			if (!count($error))
			{
				$data['password_hash'] = $phpbb_container->get('passwords.manager')->hash($data['new_password']);

				$account = $this->create_account($data, $cp_data, [
					'group'				=> $data['group'],
					'default_group'		=> $default_group,
					'new_user'			=> !empty($new_user),
					'admin_activate'	=> $admin_activate,
					'use_queue'			=> false,
				]);

				$message = [$this->language->lang($account['message'])];
				$message[] = sprintf($this->language->lang('ADDUSER_CONTINUE_EDIT_USER'), '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=overview&amp;u=' . $account['user_id']) . '">', $data['username'], '</a>');
				$message[] = sprintf($this->language->lang('ADDUSER_EDIT_USER_GROUPS'), '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=groups&amp;u=' . $account['user_id']) . '">', '</a>');
				$message[] = adm_back_link($this->u_action);

				$this->send_admin_copy([$account], false);

				trigger_error(implode('<br>', $message));
			}
		}

		if ($this->config['allow_birthdays'])
		{
			$s_birthday_day_options = '<option value="0"' . ((!$data['bday_day']) ? ' selected="selected"' : '') . '>--</option>';

			for ($i = 1; $i < 32; $i++)
			{
				$selected = ($i == $data['bday_day']) ? ' selected="selected"' : '';
				$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
			}

			$s_birthday_month_options = '<option value="0"' . ((!$data['bday_month']) ? ' selected="selected"' : '') . '>--</option>';

			for ($i = 1; $i < 13; $i++)
			{
				$selected = ($i == $data['bday_month']) ? ' selected="selected"' : '';
				$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
			}

			$now = getdate();

			$s_birthday_year_options = '<option value="0"' . ((!$data['bday_year']) ? ' selected="selected"' : '') . '>--</option>';

			for ($i = $now['year'] - 100; $i <= $now['year']; $i++)
			{
				$selected = ($i == $data['bday_year']) ? ' selected="selected"' : '';
				$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
			}

			unset($now);

			$template->assign_vars([
				'S_BIRTHDAY_DAY_OPTIONS'	=> $s_birthday_day_options,
				'S_BIRTHDAY_MONTH_OPTIONS'	=> $s_birthday_month_options,
				'S_BIRTHDAY_YEAR_OPTIONS'	=> $s_birthday_year_options,
				'S_BIRTHDAYS_ENABLED'		=> true,
			]);
		}

		// phpBB 4.0 returns the timezone and language options as arrays for the
		// template to render; phpBB 3.3 fills template blocks and returns HTML
		if (function_exists('phpbb_language_select'))
		{
			$template->assign_var('TIMEZONE_OPTIONS', [
				'tag'		=> 'select',
				'name'		=> 'tz',
				'options'	=> phpbb_timezone_select($this->user, $data['tz'], true),
			]);

			$s_lang_options = '';
			foreach (phpbb_language_select($db, $data['lang']) as $option)
			{
				$s_lang_options .= '<option value="' . $option['value'] . '"' . ($option['selected'] ? ' selected="selected"' : '') . '>' . $option['label'] . '</option>';
			}
		}
		else
		{
			phpbb_timezone_select($template, $this->user, $data['tz'], true);
			$s_lang_options = language_select($data['lang']);
		}

		$template->assign_vars([
			'ERROR'				=> (count($error)) ? implode('<br>', $error) : '',
			'NEW_USERNAME'		=> $data['username'],
			'EMAIL'				=> $data['email'],
			'PASSWORD'			=> $data['new_password'],
			'PASSWORD_CONFIRM'	=> $data['password_confirm'],

			'L_PASSWORD_EXPLAIN'	=> $this->language->lang($this->config['pass_complex'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_pass_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_pass_chars'])) . ' ' . $this->language->lang($this->config['email_enable'] ? 'ADDUSER_PASSWORD_EXPLAIN' : 'ADDUSER_PASSWORD_EXPLAIN_NO_EMAIL'),
			'L_USERNAME_EXPLAIN'	=> $this->language->lang($this->config['allow_name_chars'] . '_EXPLAIN', $this->language->lang('CHARACTERS', (int) $this->config['min_name_chars']), $this->language->lang('CHARACTERS', (int) $this->config['max_name_chars'])),
			'L_ADD_USER_EXPLAIN'	=> sprintf($this->language->lang('ADDUSER_ADD_USER_EXPLAIN'), '<a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", 'i=acp_board&amp;mode=registration') . '">', '</a>'),
			'L_REG_COND'			=> $this->get_registration_condition(),

			'S_USER_ADD'		=> true,
			'S_GROUP_OPTIONS'	=> $this->get_groups($group_selected),
			'S_LANG_OPTIONS'	=> $s_lang_options,
			'S_ADMIN_ACTIVATE'	=> $this->config['require_activation'] == USER_ACTIVATION_ADMIN && $this->config['email_enable'],
			'S_NEW_USER_SET'	=> !empty($this->config['new_member_post_limit']) ? true : false,
			'S_NEW_USER_ENABLE'	=> $new_user,

			'U_ADMIN_ACTIVATE'	=> ($admin_activate) ? 'checked="checked"' : '',
			'S_DEFAULT_GROUP'	=> $default_group,
		]);

		$this->user->profile_fields = [];

		// Generate profile fields -> Template Block Variable profile_fields
		$cp->generate_profile_fields('profile', $this->user->get_iso_lang_id());
	}

	/**
	 * The "Add users from CSV" page: upload, then check the rows, then create
	 * the accounts and show what happened.
	 *
	 * The checked rows travel from the check step to the create step in a
	 * hidden field, with passwords already hashed and an HMAC over the whole
	 * payload, so what gets created is exactly what the admin saw.
	 *
	 * @return void
	 */
	protected function csv_page()
	{
		$this->tpl_name = 'acp_adduser_csv';
		$this->page_title = $this->language->lang('ACP_ADDUSER_CSV');

		add_form_key('acp_adduser_csv');

		$options = [
			'group'				=> $this->request->variable('group', 0),
			'default_group'		=> $this->read_default_group_choice(),
			'new_user'			=> $this->config['new_member_post_limit'] && $this->request->variable('new_user', 0),
			'admin_activate'	=> $this->config['require_activation'] == USER_ACTIVATION_ADMIN && $this->request->variable('activate', 0),
			'use_queue'			=> true,
		];
		$options['new_user'] = $options['new_user'] || $options['default_group'] === 'newly_registered';
		$error = [];

		if ($this->request->is_set_post('create'))
		{
			if (!check_form_key('acp_adduser_csv'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$payload = $this->decode_csv_payload($this->request->variable('csv_data', ''), $this->request->variable('csv_hash', ''));

			if ($payload === null)
			{
				trigger_error($this->language->lang('ADDUSER_CSV_DATA_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->csv_create($payload);
			return;
		}

		if ($this->request->is_set_post('preview'))
		{
			if (!check_form_key('acp_adduser_csv'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($options['group'] && !isset($this->get_allowed_groups()[$options['group']]))
			{
				$error[] = $this->language->lang('ADDUSER_GROUP_NOT_ALLOWED');
			}

			$default_group_error = $this->check_default_group_choice($options['default_group'], $options['group']);
			if ($default_group_error !== '')
			{
				$error[] = $this->language->lang($default_group_error);
			}

			$contents = $this->read_csv_upload($error);

			if ($contents !== null && !count($error))
			{
				$reader = new \phpbbmodders\adduser\csv_reader();
				$parsed = $reader->parse($contents, self::CSV_MAX_ROWS);

				if ($parsed['error'] === null)
				{
					$this->csv_preview($parsed, $options);
					return;
				}

				// The column name in CSV_UNKNOWN_COLUMN comes straight from the file
				$error[] = $this->language->lang(...array_map(function ($argument) {
					return is_string($argument) ? utf8_htmlspecialchars($argument) : $argument;
				}, $parsed['error']));
			}
		}

		$this->template->assign_vars([
			'ERROR'				=> implode('<br>', $error),
			'L_CSV_FILE_EXPLAIN'	=> $this->language->lang('ADDUSER_CSV_FILE_EXPLAIN', self::CSV_MAX_ROWS, get_formatted_filesize(self::CSV_MAX_BYTES)),
			'L_REG_COND'		=> $this->get_registration_condition(),

			'S_STEP_UPLOAD'		=> true,
			// Lets the footer script hide "default group" while no group is chosen
			'S_USER_ADD'		=> true,
			'S_GROUP_OPTIONS'	=> $this->get_groups($options['group']),
			'S_ADMIN_ACTIVATE'	=> $this->config['require_activation'] == USER_ACTIVATION_ADMIN && $this->config['email_enable'],
			'S_NEW_USER_SET'	=> !empty($this->config['new_member_post_limit']),
			'S_NEW_USER_ENABLE'	=> $options['new_user'],
			'S_DEFAULT_GROUP'	=> $options['default_group'],
			'S_ACTIVATE'		=> $options['admin_activate'],

			'U_ACTION'			=> $this->u_action,
		]);
	}

	/**
	 * Read the uploaded CSV file.
	 *
	 * @param array $error Receives a localised message when the upload is unusable
	 * @return string|null File contents, or null on error
	 */
	protected function read_csv_upload(array &$error)
	{
		$file = $this->request->file('csv_file');
		$upload_error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;

		if ($upload_error === UPLOAD_ERR_NO_FILE || empty($file['tmp_name']) || empty($file['size']))
		{
			$error[] = $this->language->lang('ADDUSER_CSV_NO_FILE');
			return null;
		}

		if ($upload_error === UPLOAD_ERR_INI_SIZE || $upload_error === UPLOAD_ERR_FORM_SIZE || $file['size'] > self::CSV_MAX_BYTES)
		{
			$error[] = $this->language->lang('ADDUSER_CSV_TOO_LARGE', get_formatted_filesize(self::CSV_MAX_BYTES));
			return null;
		}

		if ($upload_error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name']))
		{
			$error[] = $this->language->lang('ADDUSER_CSV_UPLOAD_FAILED');
			return null;
		}

		$contents = file_get_contents($file['tmp_name']);

		if ($contents === false)
		{
			$error[] = $this->language->lang('ADDUSER_CSV_UPLOAD_FAILED');
			return null;
		}

		return $contents;
	}

	/**
	 * Check every row of a parsed CSV file and show the check page.
	 *
	 * @param array $parsed  csv_reader::parse() result without a file-level error
	 * @param array $options Options chosen on the upload page
	 * @return void
	 */
	protected function csv_preview(array $parsed, array $options)
	{
		// Hashing a password with phpBB's default algorithm takes up to a second
		// on slow servers, so a full file can need more than the usual time limit
		if (function_exists('set_time_limit'))
		{
			set_time_limit(0);
		}

		$passwords_manager = $this->phpbb_container->get('passwords.manager');
		$type_cast = new \phpbb\request\type_cast_helper();
		$seen_usernames = $seen_emails = [];
		$ready = $skipped = [];

		foreach ($parsed['rows'] as $row)
		{
			$values = $row['values'];

			// Escape and normalise the values exactly as request::variable() does on
			// the single form, so both pages store accounts the same way
			$type_cast->set_var($username, $values['username'], 'string', true);
			$type_cast->set_var($email, $values['email'], 'string', false);
			$type_cast->set_var($password, $values['password'], 'string', true);
			$type_cast->set_var($lang, $values['lang'], 'string', false);
			$type_cast->set_var($birthday, $values['birthday'], 'string', false);

			$data = [
				'username'		=> $username,
				'email'			=> strtolower($email),
				'new_password'	=> $password,
				'lang'			=> ($lang === '') ? basename($this->config['default_lang']) : basename($lang),
				'tz'			=> $this->config['board_timezone'],
			];

			$errors = [];

			if ($row['error'] !== null)
			{
				$errors[] = $this->language->lang(...$row['error']);
			}

			$password_generated = ($data['new_password'] === '');

			// Without board email a generated password could never reach the user
			if ($password_generated && !$this->config['email_enable'])
			{
				$errors[] = $this->language->lang('ADDUSER_CSV_PASSWORD_REQUIRED');
			}
			else if ($password_generated)
			{
				$data['new_password'] = $this->generate_password();
			}

			$errors = array_merge($errors, $this->validate_csv_account($data, $birthday, $data['new_password'] !== ''));

			$username_clean = utf8_clean_string($data['username']);

			if ($username_clean !== '' && isset($seen_usernames[$username_clean]))
			{
				$errors[] = $this->language->lang('ADDUSER_CSV_DUPLICATE_USERNAME', $seen_usernames[$username_clean]);
			}
			else if ($username_clean !== '')
			{
				$seen_usernames[$username_clean] = $row['row'];
			}

			if (!$this->config['allow_emailreuse'] && $data['email'] !== '')
			{
				if (isset($seen_emails[$data['email']]))
				{
					$errors[] = $this->language->lang('ADDUSER_CSV_DUPLICATE_EMAIL', $seen_emails[$data['email']]);
				}
				else
				{
					$seen_emails[$data['email']] = $row['row'];
				}
			}

			$this->template->assign_block_vars('csv_rows', [
				'ROW'			=> $row['row'],
				'USERNAME'		=> $data['username'],
				'EMAIL'			=> $data['email'],
				'LANG'			=> $data['lang'],
				'BIRTHDAY'		=> $this->config['allow_birthdays'] ? $birthday : '',
				'PASSWORD'		=> $this->language->lang($password_generated ? 'ADDUSER_CSV_PASSWORD_GENERATED' : 'ADDUSER_CSV_PASSWORD_GIVEN'),
				'ERRORS'		=> implode('<br>', $errors),
			]);

			if (count($errors))
			{
				$skipped[] = ['row' => $row['row'], 'username' => $data['username'], 'errors' => $errors];
				continue;
			}

			$ready[] = [
				'row'			=> $row['row'],
				'username'		=> $data['username'],
				'email'			=> $data['email'],
				'lang'			=> $data['lang'],
				'birthday'		=> $this->config['allow_birthdays'] ? $birthday : '',
				'password_hash'	=> $passwords_manager->hash($data['new_password']),
			];
		}

		$payload = $this->encode_csv_payload(['options' => $options, 'ready' => $ready, 'skipped' => $skipped]);

		$this->template->assign_vars([
			'L_CSV_CREATE'			=> $this->language->lang('ADDUSER_CSV_CREATE_ACCOUNTS', count($ready)),
			'L_CSV_PREVIEW_COUNTS'	=> $this->language->lang('ADDUSER_CSV_READY_COUNT', count($ready)) . ' ' . $this->language->lang('ADDUSER_CSV_SKIPPED_COUNT', count($skipped)),
			'L_REG_COND'			=> $this->get_registration_condition(),

			'S_STEP_PREVIEW'		=> true,
			'S_BIRTHDAYS_ENABLED'	=> (bool) $this->config['allow_birthdays'],
			'S_BIRTHDAYS_IGNORED'	=> !$this->config['allow_birthdays'] && in_array('birthday', $parsed['columns'], true),
			'S_CAN_CREATE'			=> count($ready) > 0,

			'CSV_DATA'				=> $payload['data'],
			'CSV_HASH'				=> $payload['hash'],

			'U_ACTION'				=> $this->u_action,
		]);
	}

	/**
	 * Create the accounts of a checked CSV file and show the summary page.
	 *
	 * Every row is checked again first: a username or email address may have
	 * been taken since the check page was shown.
	 *
	 * @param array $payload Decoded payload from the check page
	 * @return void
	 */
	protected function csv_create(array $payload)
	{
		// Creating and emailing a full file can take longer than the usual time limit
		if (function_exists('set_time_limit'))
		{
			set_time_limit(0);
		}

		$options = [
			'group'				=> (int) $payload['options']['group'],
			'default_group'		=> (string) $payload['options']['default_group'],
			'new_user'			=> !empty($payload['options']['new_user']),
			'admin_activate'	=> !empty($payload['options']['admin_activate']),
			'use_queue'			=> true,
		];

		// Check again: the group may have been deleted, or the board settings
		// changed, since the check page
		if ($options['group'] && !isset($this->get_allowed_groups()[$options['group']]))
		{
			trigger_error($this->language->lang('ADDUSER_GROUP_NOT_ALLOWED') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$default_group_error = $this->check_default_group_choice($options['default_group'], $options['group']);
		if ($default_group_error !== '')
		{
			trigger_error($this->language->lang($default_group_error) . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$created = 0;
		$accounts = [];
		$skipped = $payload['skipped'];

		foreach ($payload['ready'] as $row)
		{
			$data = [
				'username'		=> $row['username'],
				'email'			=> $row['email'],
				'lang'			=> $row['lang'],
				'tz'			=> $this->config['board_timezone'],
				'password_hash'	=> $row['password_hash'],
			];

			$errors = $this->validate_csv_account($data, $row['birthday'], false);

			if (count($errors))
			{
				$skipped[] = ['row' => $row['row'], 'username' => $row['username'], 'errors' => $errors];
				continue;
			}

			$account = $this->create_account($data, [], $options);
			$accounts[] = $account;
			$created++;

			$this->template->assign_block_vars('csv_created', [
				'ROW'		=> $row['row'],
				'USERNAME'	=> $row['username'],
				'EMAIL'		=> $row['email'],
				'U_USER'	=> append_sid("{$this->phpbb_admin_path}index.{$this->php_ext}", 'i=users&amp;mode=overview&amp;u=' . $account['user_id']),
			]);
		}

		$this->send_admin_copy($accounts, true);

		usort($skipped, function ($a, $b) {
			return $a['row'] <=> $b['row'];
		});

		foreach ($skipped as $row)
		{
			$this->template->assign_block_vars('csv_skipped', [
				'ROW'		=> $row['row'],
				'USERNAME'	=> $row['username'],
				'ERRORS'	=> implode('<br>', $row['errors']),
			]);
		}

		$this->template->assign_vars([
			'L_CSV_SUMMARY_COUNTS'	=> $this->language->lang('ADDUSER_CSV_CREATED_COUNT', $created) . ' ' . $this->language->lang('ADDUSER_CSV_SKIPPED_COUNT', count($skipped)),
			'L_REG_COND'			=> $this->get_registration_condition(),

			'S_STEP_SUMMARY'		=> true,

			'U_ACTION'				=> $this->u_action,
		]);
	}

	/**
	 * Check one CSV account with the rules of the single form.
	 *
	 * @param array  $data           username, email, lang, tz and, when $check_password is true, new_password;
	 *                               user_birthday is added when birthdays are enabled and one is given
	 * @param string $birthday       Birthday as written in the file (DD-MM-YYYY), or ''
	 * @param bool   $check_password Whether to check new_password against the password rules
	 * @return string[] Localised errors, empty when the account can be created
	 */
	protected function validate_csv_account(array &$data, $birthday, $check_password)
	{
		$errors = [];
		$validate_array = $this->get_validate_array();

		if (!$check_password)
		{
			unset($validate_array['new_password']);
		}

		unset($validate_array['bday_day'], $validate_array['bday_month'], $validate_array['bday_year'], $validate_array['user_birthday']);

		if ($this->config['allow_birthdays'] && $birthday !== '')
		{
			if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $birthday, $match))
			{
				$data['bday_day'] = (int) $match[1];
				$data['bday_month'] = (int) $match[2];
				$data['bday_year'] = (int) $match[3];
				$data['user_birthday'] = sprintf('%2d-%2d-%4d', $data['bday_day'], $data['bday_month'], $data['bday_year']);

				$validate_array = array_merge($validate_array, $this->get_birthday_validate_array());
			}
			else
			{
				$errors[] = $this->language->lang('ADDUSER_CSV_BIRTHDAY_FORMAT');
			}
		}

		$validation_errors = validate_data($data, $validate_array);
		$errors = array_merge($errors, array_map([$this->language, 'lang'], $validation_errors));

		// language_iso_name above checks the language is installed; the
		// extension also needs its email templates in that language
		if (!in_array('WRONG_DATA_LANG', $validation_errors, true) && !$this->extension_has_language($data['lang']))
		{
			$errors[] = $this->language->lang('ADDUSER_CSV_LANG_NOT_AVAILABLE', $data['lang']);
		}

		return array_values(array_unique($errors));
	}

	/**
	 * Pack the checked rows for the hidden field of the check page.
	 *
	 * @param array $payload Options, ready rows and skipped rows
	 * @return array ['data' => string, 'hash' => string]
	 */
	protected function encode_csv_payload(array $payload)
	{
		$data = base64_encode(json_encode($payload));

		return ['data' => $data, 'hash' => hash_hmac('sha256', $data, $this->get_csv_payload_key())];
	}

	/**
	 * Unpack the hidden field of the check page.
	 *
	 * @param string $data Base64-encoded JSON payload
	 * @param string $hash HMAC of $data
	 * @return array|null Payload, or null when it was changed or is malformed
	 */
	protected function decode_csv_payload($data, $hash)
	{
		if ($data === '' || !hash_equals(hash_hmac('sha256', $data, $this->get_csv_payload_key()), $hash))
		{
			return null;
		}

		$payload = json_decode((string) base64_decode($data, true), true);

		if (!is_array($payload) || !isset($payload['options'], $payload['ready'], $payload['skipped']) || !is_array($payload['ready']) || !is_array($payload['skipped']))
		{
			return null;
		}

		return $payload;
	}

	/**
	 * Key for the HMAC over the checked rows: secret to the board and to the
	 * admin who uploaded the file.
	 *
	 * @return string
	 */
	protected function get_csv_payload_key()
	{
		return 'phpbbmodders_adduser_csv' . $this->config['avatar_salt'] . $this->user->data['user_form_salt'] . $this->user->data['user_id'];
	}

	/**
	 * Create one account, add it to the chosen group, log it and email the user.
	 *
	 * @param array $data    username, email, lang, tz, password_hash and, with birthdays enabled, optionally user_birthday
	 * @param array $cp_data Custom profile field values
	 * @param array $options group (int, 0 for none), default_group (registered, newly_registered or group),
	 *                       new_user, admin_activate and use_queue (bool)
	 * @return array [
	 *     'user_id'  => int,
	 *     'username' => string,
	 *     'email'    => string,
	 *     'message'  => language key describing what the user was sent,
	 *     'emailed'  => bool, whether a welcome email was sent
	 * ]
	 */
	protected function create_account(array $data, array $cp_data, array $options)
	{
		$admin_activate = $options['admin_activate'];
		$server_url = generate_board_url();

		$group_id = $this->get_special_group_id('REGISTERED');

		// Use group_id here
		if (!$group_id)
		{
			trigger_error('NO_GROUP');
		}

		if (($this->config['require_activation'] == USER_ACTIVATION_SELF || $this->config['require_activation'] == USER_ACTIVATION_ADMIN) && $this->config['email_enable'] && !$admin_activate)
		{
			$user_actkey = gen_rand_string(mt_rand(6, 10));
			$user_type = USER_INACTIVE;
			$user_inactive_reason = INACTIVE_REGISTER;
			$user_inactive_time = time();
		}
		else
		{
			$user_type = USER_NORMAL;
			$user_actkey = '';
			$user_inactive_reason = 0;
			$user_inactive_time = 0;
		}

		$user_row = [
			'username'				=> $data['username'],
			'user_password'			=> $data['password_hash'],
			'user_email'			=> $data['email'],
			'group_id'				=> (int) $group_id,
			'user_timezone'			=> $data['tz'],
			'user_lang'				=> $data['lang'],
			'user_type'				=> $user_type,
			'user_actkey'			=> $user_actkey,
			'user_ip'				=> $this->user->ip,
			'user_regdate'			=> time(),
			'user_inactive_reason'	=> $user_inactive_reason,
			'user_inactive_time'	=> $user_inactive_time,
		];

		if ($this->config['allow_birthdays'] && isset($data['user_birthday']))
		{
			$user_row['user_birthday'] = $data['user_birthday'];
		}

		if ($this->config['new_member_post_limit'] && ($options['new_user'] || $options['default_group'] === 'newly_registered'))
		{
			$user_row['user_new'] = 1;
		}

		// Register user
		$user_id = user_add($user_row, $cp_data);

		// This should not happen because the required variables are listed above
		if ($user_id === false)
		{
			trigger_error($this->language->lang('NO_USER'), E_USER_ERROR);
		}

		if (!empty($options['group']))
		{
			group_user_add($options['group'], [$user_id]);
		}

		// Set the chosen default group. user_add() may already have made Newly
		// registered users the default, following the board's own setting; the
		// admin's choice here wins either way.
		switch ($options['default_group'])
		{
			case 'group':
				$default_group_id = (int) $options['group'];
			break;

			case 'newly_registered':
				$default_group_id = $this->get_special_group_id('NEWLY_REGISTERED');
			break;

			default:
				$default_group_id = (int) $group_id;
			break;
		}

		if ($default_group_id)
		{
			group_set_user_default($default_group_id, [$user_id]);
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_USER_ADDED', time(), [$data['username']]);

		// Send a message to the user if needed
		$send_reset_link = true;

		if (!$this->config['email_enable'])
		{
			// The account is active (see above) and nothing is sent
			$message = 'ACP_ADDUSER_ACCOUNT_ADDED_NO_EMAIL';
		}
		else if ($this->config['require_activation'] == USER_ACTIVATION_SELF)
		{
			$message = 'ACP_ADDUSER_ACCOUNT_INACTIVE';
			$email_template = '@phpbbmodders_adduser/user_added_inactive';
			// Activating the account clears reset_token (ucp_activate), and the reset
			// form refuses inactive users, so a link could never be used here. The
			// email tells the user to use "Forgot password" once activated instead.
			$send_reset_link = false;
		}
		else if ($this->config['require_activation'] == USER_ACTIVATION_ADMIN && !$admin_activate)
		{
			$message = 'ACP_ADDUSER_ACCOUNT_INACTIVE_ADMIN';
			$email_template = '@phpbbmodders_adduser/user_added_admin_welcome_inactive';
		}
		else
		{
			$message = 'ACP_ADDUSER_ACCOUNT_ADDED';
			$email_template = '@phpbbmodders_adduser/user_added_welcome';
		}

		if ($this->config['email_enable'])
		{
			$email_vars = [
				'WELCOME_MSG'		=> htmlspecialchars_decode(sprintf($this->language->lang('WELCOME_SUBJECT'), $this->config['sitename'])),
				'USERNAME'			=> htmlspecialchars_decode($data['username']),

				'U_ACTIVATE'		=> "$server_url/ucp.{$this->php_ext}?mode=activate&u=$user_id&k=$user_actkey",
			];

			if ($send_reset_link)
			{
				// Send a reset link instead of the password: the user picks their own.
				// route() ends in append_sid(), which would attach the session id of the
				// admin creating the account. That must not travel by email.
				$reset_expires = \phpbb\user::get_token_expiration();
				$reset_token = strtolower(gen_rand_string(32));
				$this->db->sql_query('UPDATE ' . USERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', [
						'reset_token'				=> $reset_token,
						'reset_token_expiration'	=> (int) $reset_expires,
					]) . '
					WHERE user_id = ' . (int) $user_id);
				global $_SID;
				$sid_backup = $_SID;
				$_SID = '';
				$reset_link = generate_board_url(true) . $this->phpbb_container->get('controller.helper')->route(
					'phpbb_ucp_reset_password_controller',
					['u' => (int) $user_id, 'token' => $reset_token],
					false
				);
				$_SID = $sid_backup;

				$email_vars['U_RESET_PASSWORD'] = $reset_link;
				$email_vars['RESET_EXPIRES'] = $this->user->format_date($reset_expires, str_replace('|', '', $this->language->lang('DATETIME_FORMAT')));
			}

			$this->send_email($email_template, $data['lang'], $data['email'], $data['username'], $email_vars, $options['use_queue']);
		}

		// Only an inactive account waits for an administrator; without board email
		// the account is created active
		if ($user_type == USER_INACTIVE && $this->config['require_activation'] == USER_ACTIVATION_ADMIN)
		{
			$phpbb_notifications = $this->phpbb_container->get('notification_manager');
			$phpbb_notifications->add_notifications('notification.type.admin_activate_user', [
				'user_id'		=> $user_id,
				'user_actkey'	=> $user_row['user_actkey'],
				'user_regdate'	=> $user_row['user_regdate'],
			]);
		}

		return [
			'user_id'	=> (int) $user_id,
			'username'	=> $data['username'],
			'email'		=> $data['email'],
			'message'	=> $message,
			'emailed'	=> (bool) $this->config['email_enable'],
		];
	}

	/**
	 * Id of one of phpBB's special groups, looked up once per request: the CSV
	 * page creates many accounts in one go.
	 *
	 * @param string $group_name Special group name, e.g. REGISTERED
	 * @return int 0 when the group is missing
	 */
	protected function get_special_group_id($group_name)
	{
		if (!isset($this->special_group_ids[$group_name]))
		{
			$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = '" . $this->db->sql_escape($group_name) . "'
					AND group_type = " . GROUP_SPECIAL;
			$result = $this->db->sql_query($sql, 3600);
			$this->special_group_ids[$group_name] = (int) $this->db->sql_fetchfield('group_id');
			$this->db->sql_freeresult($result);
		}

		return $this->special_group_ids[$group_name];
	}

	/**
	 * Read the posted default-group choice. On a fresh form it follows the
	 * board's "Set Newly registered users group to default" setting.
	 *
	 * @return string registered, newly_registered or group
	 */
	protected function read_default_group_choice()
	{
		$board_default = ($this->config['new_member_post_limit'] && $this->config['new_member_group_default']) ? 'newly_registered' : 'registered';
		$choice = $this->request->variable('default_group', $board_default);

		return in_array($choice, ['registered', 'newly_registered', 'group'], true) ? $choice : 'registered';
	}

	/**
	 * Check that a default-group choice can be applied.
	 *
	 * @param string $choice registered, newly_registered or group
	 * @param int    $group  Group chosen in the group selector, 0 for none
	 * @return string Language key of the problem, '' when the choice is fine
	 */
	protected function check_default_group_choice($choice, $group)
	{
		if ($choice === 'group' && !$group)
		{
			return 'ADDUSER_DEFAULT_GROUP_NONE';
		}

		// Newly registered users only exists for accounts while the post limit is on
		if ($choice === 'newly_registered' && !$this->config['new_member_post_limit'])
		{
			return 'ADDUSER_DEFAULT_GROUP_UNAVAILABLE';
		}

		if (!in_array($choice, ['registered', 'newly_registered', 'group'], true))
		{
			return 'ADDUSER_DEFAULT_GROUP_UNAVAILABLE';
		}

		return '';
	}

	/**
	 * Email the admin who created accounts a copy confirming the welcome
	 * emails, when the board setting asks for it. One email covers every
	 * account of a request, so a CSV file sends one, not one per row. The
	 * welcome emails' activation and set-password links are left out: they
	 * would let anyone holding the copy take the accounts over.
	 *
	 * @param array $accounts  create_account() results
	 * @param bool  $use_queue Whether to put the email in the board's queue
	 * @return void
	 */
	protected function send_admin_copy(array $accounts, $use_queue)
	{
		$accounts = array_filter($accounts, function ($account) {
			return $account['emailed'];
		});

		if (!$this->config['adduser_admin_copy'] || !$this->config['email_enable'] || !count($accounts) || empty($this->user->data['user_email']))
		{
			return;
		}

		$kinds = [
			'ACP_ADDUSER_ACCOUNT_ADDED'				=> 'ADDUSER_COPY_KIND_WELCOME',
			'ACP_ADDUSER_ACCOUNT_INACTIVE'			=> 'ADDUSER_COPY_KIND_ACTIVATE',
			'ACP_ADDUSER_ACCOUNT_INACTIVE_ADMIN'	=> 'ADDUSER_COPY_KIND_ADMIN_ACTIVATE',
		];
		$lines = [];

		foreach ($accounts as $account)
		{
			$lines[] = '- ' . htmlspecialchars_decode($account['username']) . ' <' . $account['email'] . '>: ' . htmlspecialchars_decode($this->language->lang($kinds[$account['message']]));
		}

		$lang = $this->extension_has_language($this->user->data['user_lang']) ? $this->user->data['user_lang'] : 'en';

		$this->send_email('@phpbbmodders_adduser/user_added_admin_copy', $lang, $this->user->data['user_email'], $this->user->data['username'], [
			'USERNAME'	=> htmlspecialchars_decode($this->user->data['username']),
			'ACCOUNTS'	=> implode("\n", $lines),
		], $use_queue);
	}

	/**
	 * Make a random password that meets the board's password rules.
	 *
	 * The password is never emailed, the user sets their own via the reset
	 * link. Draw it from random_int rather than from the current time, and
	 * take one character from every class validate_password() demands.
	 *
	 * @return string
	 */
	protected function generate_password()
	{
		$pools = ['abcdefghijkmnopqrstuvwxyz', 'ABCDEFGHJKLMNPQRSTUVWXYZ', '23456789'];
		if (!in_array($this->config['pass_complex'], ['PASS_TYPE_ANY', 'PASS_TYPE_CASE', 'PASS_TYPE_ALPHA'], true))
		{
			// PASS_TYPE_SYMBOL, or an invalid setting, which phpBB treats as the strictest one
			$pools[] = '{}[];:,./<>?_+~!@#';
		}
		$all = implode('', $pools);
		$length = max(20, (int) $this->config['min_pass_chars']);
		$new_password = '';
		foreach ($pools as $pool)
		{
			$new_password .= $pool[random_int(0, strlen($pool) - 1)];
		}
		for ($i = strlen($new_password); $i < $length; $i++)
		{
			$new_password .= $all[random_int(0, strlen($all) - 1)];
		}
		// str_shuffle() draws from the Mersenne Twister, so shuffle by hand
		for ($i = strlen($new_password) - 1; $i > 0; $i--)
		{
			$j = random_int(0, $i);
			$swap = $new_password[$i];
			$new_password[$i] = $new_password[$j];
			$new_password[$j] = $swap;
		}

		return $new_password;
	}

	/**
	 * validate_data() rules shared by the single form and the CSV page.
	 *
	 * @return array
	 */
	protected function get_validate_array()
	{
		$validate_array = [
			'username'			=> [
				['string', false, $this->config['min_name_chars'], $this->config['max_name_chars']],
				['username', '']],
			'email'				=> [
				['string', false, 6, 60],
				['user_email']],
			'new_password'		=> [
				['string', false, $this->config['min_pass_chars'], $this->config['max_pass_chars']],
				['password']],
			'tz'				=> ['timezone'],
			'lang'				=> ['language_iso_name'],
		];

		if ($this->config['allow_birthdays'])
		{
			$validate_array = array_merge($validate_array, $this->get_birthday_validate_array());
		}

		return $validate_array;
	}

	/**
	 * validate_data() rules for a birthday.
	 *
	 * @return array
	 */
	protected function get_birthday_validate_array()
	{
		return [
			'bday_day'		=> ['num', true, 1, 31],
			'bday_month'	=> ['num', true, 1, 12],
			'bday_year'		=> ['num', true, 1901, gmdate('Y', time()) + 50],
			'user_birthday'	=> ['date', true],
		];
	}

	/**
	 * Explanation of what the board's activation setting means for a new account.
	 *
	 * @return string
	 */
	protected function get_registration_condition()
	{
		// Activation needs email, so without it every account is active at once
		if (!$this->config['email_enable'])
		{
			return $this->language->lang('ACP_ADDUSER_NO_EMAIL');
		}

		switch ($this->config['require_activation'])
		{
			case USER_ACTIVATION_SELF:
				return $this->language->lang('ACP_ADDUSER_EMAIL_ACTIVATE');

			case USER_ACTIVATION_ADMIN:
				return $this->language->lang('ACP_ADDUSER_ADMIN_ACTIVATE');

			default:
				return $this->language->lang('ACP_ADDUSER_INSTANT_ACTIVATE');
		}
	}

	/**
	 * Send one email to a new user.
	 *
	 * phpBB 4.0 replaced the messenger class with the messenger.method.email
	 * service, so use the service where it exists and the class otherwise.
	 *
	 * @param string $template_name Email template, e.g. @phpbbmodders_adduser/user_added_welcome
	 * @param string $lang          Language of the template
	 * @param string $address       Recipient email address
	 * @param string $name          Recipient name
	 * @param array  $vars          Template variables
	 * @param bool   $use_queue     Whether to put the email in the board's queue instead of sending it now
	 * @return void
	 */
	private function send_email($template_name, $lang, $address, $name, array $vars, $use_queue = false)
	{
		if ($this->phpbb_container->has('messenger.method.email'))
		{
			$email = $this->phpbb_container->get('messenger.method.email');
			$email->set_use_queue($use_queue);
			$email->template($template_name, $lang);
			$email->to($address, $name);
			$email->anti_abuse_headers($this->config, $this->user);
			$email->assign_vars($vars);
			$email->send();
			$email->save_queue();
			return;
		}

		if (!class_exists('messenger'))
		{
			include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ext);
		}

		$messenger = new \messenger($use_queue);
		$messenger->template($template_name, $lang);
		$messenger->to($address, $name);
		$messenger->headers('X-AntiAbuse: Board servername - ' . $this->config['server_name']);
		$messenger->headers('X-AntiAbuse: User_id - ' . $this->user->data['user_id']);
		$messenger->headers('X-AntiAbuse: Username - ' . $this->user->data['username']);
		$messenger->headers('X-AntiAbuse: User IP - ' . $this->user->ip);
		$messenger->assign_vars($vars);
		$messenger->send(NOTIFY_EMAIL);
		$messenger->save_queue();
	}

	/**
	 * Groups a new account may be added to: all but the special groups phpBB
	 * manages itself, and the COPPA group only while COPPA is enabled.
	 *
	 * @return array Group rows keyed by group_id
	 */
	protected function get_allowed_groups()
	{
		$ignore_groups = ['BOTS', 'GUESTS', 'REGISTERED', 'NEWLY_REGISTERED'];

		$sql = 'SELECT group_name, group_id, group_type
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $this->db->sql_in_set('group_name', $ignore_groups, true) . '
			ORDER BY group_name ASC';
		$result = $this->db->sql_query($sql);

		$groups = [];

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!$this->config['coppa_enable'] && $row['group_name'] == 'REGISTERED_COPPA')
			{
				continue;
			}

			$groups[(int) $row['group_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $groups;
	}

	/**
	 * Build the group selector.
	 *
	 * @param int $group_selected Group to preselect, 0 for none
	 * @return string HTML select element
	 */
	private function get_groups($group_selected)
	{
		$s_group_options = '<select id="group" name="group"><option value="0">' . $this->language->lang('NO_GROUP') . '</option>';

		foreach ($this->get_allowed_groups() as $row)
		{
			$selected = $row['group_id'] == $group_selected ? ' selected="selected"' : '';
			$s_group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '"' . $selected . '>' . (($row['group_type'] == GROUP_SPECIAL) ? $this->language->lang('G_' . $row['group_name']) : $row['group_name']) . '</option>';
		}

		$s_group_options .= '</select>';

		return $s_group_options;
	}

	/**
	 * Whether the extension has language files, and so email templates, for a language.
	 *
	 * @param string $lang Language directory name, e.g. en
	 * @return bool
	 */
	protected function extension_has_language($lang)
	{
		return in_array($lang, $this->dir_to_array($this->phpbb_root_path . 'ext/phpbbmodders/adduser/language'), true);
	}

	/**
	 * Get an array that represents directory tree
	 */
	public function dir_to_array($directory)
	{
		$directories = glob($directory . '/*', GLOB_ONLYDIR);
		$dir_array = [];

		foreach ($directories as $value)
		{
			$dir_array[] = substr(strrchr($value, '/'), 1);
		}

		return $dir_array;
	}
}
