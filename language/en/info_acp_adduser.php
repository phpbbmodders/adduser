<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	// ACP Module
	'ACP_ADD_USER'					=> 'Add User',
	'ADDUSER_DIR_NOT_EXIST'					=> 'The language you have chosen %1$s does not have the files needed for the extension. Please translate them and upload to the %2$s directory of the extension.',
	'ACP_ADDUSER_ACCOUNT_ADDED'				=> 'The user account has been created. A link was sent to the email address you provided; the user sets their own password with it. The email states how long the link is valid.',
	'ACP_ADDUSER_ACCOUNT_INACTIVE'			=> 'The user account has been created. However, the forum settings require the user to activate their account.<br>An activation key has been sent to the email address you provided for the user. After activating, the user sets their own password with “Forgot password”.',
	'ACP_ADDUSER_ACCOUNT_INACTIVE_ADMIN'	=> 'The user account has been created. However, the forum settings require account activation by an administrator.<br>An email has been sent to the Administrators and the user will be informed when their account has been activated.',
	'ACP_ADDUSER_ACCOUNT_ADDED_NO_EMAIL'	=> 'The user account has been created and is active. Board email is disabled, so no email was sent: give the user their username and password yourself.',
	'ACP_ADDUSER_ADMIN_ACTIVATE'			=> 'An email will be dispatched to an Administrator for account activation. Alternatively you may check the activate account box below to activate the account instantly once created. The user will receive a link to set their own password.',
	'ACP_ADDUSER_EMAIL_ACTIVATE'			=> 'Once the account has been created, the user will receive an email containing an activation link to activate the account. After activating, they set their own password with “Forgot password”.',
	'ACP_ADDUSER_NO_EMAIL'				=> 'Board email is disabled, so the account is active at once and no email is sent. Enter a password and give it to the user yourself.',
	'ACP_ADDUSER_INSTANT_ACTIVATE'			=> 'The account will be activated instantly. The user will receive a link to set their own password.',

	'ADDUSER_ADD_USER'					=> 'Add User',
	'ADDUSER_ADD_USER_EXPLAIN'			=> 'Create a new user account. If your activation settings are to Admin Activation only, you will have the option to activate the user instantly.',
	'ADDUSER_ADMIN_ACTIVATE'			=> 'Activate user account',
	'ADDUSER_EDIT_USER_GROUPS'			=> '%sClick here to edit the user groups for this user%s',
	'ADDUSER_GROUP_ADD'					=> 'Making a selection here will add the user to the group selected as well as to the registered users group.',
	'ADDUSER_CONTINUE_EDIT_USER'		=> '%1$sClick here to manage %2$s’s profile%3$s',
	'ADDUSER_PASSWORD_EXPLAIN'			=> 'If left blank a password will be generated. It is never emailed — the user sets their own password with the link.',
	'ADDUSER_PASSWORD_EXPLAIN_NO_EMAIL'	=> 'Required while board email is disabled: no email can reach the user, so give them the password yourself.',
	'ADDUSER_PASSWORD_REQUIRED'			=> 'Board email is disabled, so enter a password: a generated one could never reach the user.',
	'ADDUSER_ENABLE_NEWUSER'			=> 'Enable new user',
	'ADDUSER_ENABLE_NEWUSER_EXPLAIN'	=> 'If set as yes the user will be added to the newly registered user group.',

	'ADDUSER_GROUP_NOT_ALLOWED'			=> 'New accounts cannot be added to the selected group.',

	// Default group
	'ADDUSER_DEFAULT_GROUP'				=> 'Default group',
	'ADDUSER_DEFAULT_GROUP_EXPLAIN'		=> 'The group the account shows as its own; it sets the rank and colour. Newly registered users also marks the account as a new user.',
	'ADDUSER_DEFAULT_GROUP_SELECTED'	=> 'The group chosen above',
	'ADDUSER_DEFAULT_GROUP_NONE'		=> 'Choose a group above to make it the default group.',
	'ADDUSER_DEFAULT_GROUP_UNAVAILABLE'	=> 'The chosen default group is not available on this board.',

	// Administrator copy of welcome emails
	'ADDUSER_ADMIN_COPY'				=> 'Send a copy of Add User welcome emails',
	'ADDUSER_ADMIN_COPY_EXPLAIN'		=> 'When the Add User extension creates accounts, the administrator who created them gets one email listing the accounts and which welcome email each was sent. The copy leaves out the activation and set-password links.',
	'ADDUSER_COPY_KIND_WELCOME'			=> 'a link to set their own password',
	'ADDUSER_COPY_KIND_ACTIVATE'		=> 'an activation link; they set their password with “Forgot password” after activating',
	'ADDUSER_COPY_KIND_ADMIN_ACTIVATE'	=> 'a notice that an administrator must activate the account, with a link to set their password once activated',

	// Add users from CSV
	'ACP_ADDUSER_CSV'			=> 'Add users from CSV',
	'ADDUSER_ADD_USERS_CSV'				=> 'Add users from CSV',
	'ADDUSER_ADD_USERS_CSV_EXPLAIN'		=> 'Create many user accounts at once from a CSV file. Every account is checked and created the same way as on the Add User page. You see every row and its errors before anything is created. Custom profile fields are not set; edit the users afterwards if they need them.',
	'ADDUSER_CSV_FILE'					=> 'CSV file',
	'ADDUSER_CSV_FILE_EXPLAIN'			=> 'One user per row, UTF-8, with commas or semicolons between the fields. The first row names the columns, in any order: <strong>username</strong> and <strong>email</strong> are required, <strong>password</strong>, <strong>lang</strong> (a language code such as en) and <strong>birthday</strong> (DD-MM-YYYY) are optional. A blank password gets a random one that is never emailed. At most %1$d rows and %2$s per file. To use an Excel or other spreadsheet, save it as “CSV UTF-8” first.',
	'ADDUSER_CSV_OPTIONS'				=> 'Options for every account',
	'ADDUSER_CSV_CHECK_ROWS'			=> 'Check rows',
	'ADDUSER_CSV_CREATE_ACCOUNTS'		=> [
		1	=> 'Create %d account',
		2	=> 'Create %d accounts',
	],
	'ADDUSER_CSV_PREVIEW'				=> 'Check the rows',
	'ADDUSER_CSV_PREVIEW_EXPLAIN'		=> 'Nothing has been created yet. Rows with errors are skipped; to add those users, correct the rows and upload them again afterwards.',
	'ADDUSER_CSV_READY_COUNT'			=> [
		0	=> 'No rows are ready.',
		1	=> '%d row is ready.',
		2	=> '%d rows are ready.',
	],
	'ADDUSER_CSV_SKIPPED_COUNT'			=> [
		0	=> 'No rows are skipped.',
		1	=> '%d row is skipped.',
		2	=> '%d rows are skipped.',
	],
	'ADDUSER_CSV_CREATED_COUNT'			=> [
		0	=> 'No accounts were created.',
		1	=> '%d account was created.',
		2	=> '%d accounts were created.',
	],
	'ADDUSER_CSV_SUMMARY'				=> 'Accounts created',
	'ADDUSER_CSV_CREATED'				=> 'Created',
	'ADDUSER_CSV_SKIPPED'				=> 'Skipped',
	'ADDUSER_CSV_ROW'					=> 'Row',
	'ADDUSER_CSV_STATUS'				=> 'Status',
	'ADDUSER_CSV_READY'					=> 'Ready',
	'ADDUSER_CSV_PASSWORD_GIVEN'		=> 'From the file',
	'ADDUSER_CSV_PASSWORD_GENERATED'	=> 'Generated',
	'ADDUSER_CSV_UPLOAD_ANOTHER'		=> 'Upload another file',
	'ADDUSER_CSV_BIRTHDAYS_IGNORED'		=> 'Birthdays are disabled on this board, so the birthday column is ignored.',

	'ADDUSER_CSV_NO_FILE'				=> 'Choose a CSV file to upload.',
	'ADDUSER_CSV_TOO_LARGE'				=> 'The file is larger than %s.',
	'ADDUSER_CSV_UPLOAD_FAILED'			=> 'The file could not be uploaded.',
	'ADDUSER_CSV_EMPTY'					=> 'The file is empty.',
	'ADDUSER_CSV_NOT_UTF8'				=> 'The file is not UTF-8 text. Save it as “CSV UTF-8” and upload it again.',
	'ADDUSER_CSV_UNKNOWN_COLUMN'		=> 'The first row names a column “%1$s”. It must name the columns, using only: %2$s.',
	'ADDUSER_CSV_DUPLICATE_COLUMN'		=> 'The first row names the column “%s” more than once.',
	'ADDUSER_CSV_MISSING_COLUMN'		=> 'The first row has no “%s” column. It must name the columns, and username and email are required.',
	'ADDUSER_CSV_TOO_MANY_ROWS'			=> 'The file has more than %d rows. Split it into smaller files.',
	'ADDUSER_CSV_NO_ROWS'				=> 'The file has no users below the first row.',
	'ADDUSER_CSV_TOO_MANY_FIELDS'		=> 'This row has more fields than the %d columns named in the first row.',
	'ADDUSER_CSV_PASSWORD_REQUIRED'		=> 'Board email is disabled, so this row needs a password: a generated one could never reach the user.',
	'ADDUSER_CSV_BIRTHDAY_FORMAT'		=> 'The birthday must be written as DD-MM-YYYY.',
	'ADDUSER_CSV_DUPLICATE_USERNAME'	=> 'The same username is on row %d.',
	'ADDUSER_CSV_DUPLICATE_EMAIL'		=> 'The same email address is on row %d.',
	'ADDUSER_CSV_LANG_NOT_AVAILABLE'	=> 'The Add User emails are not available in the language “%s”.',
	'ADDUSER_CSV_DATA_INVALID'			=> 'The checked rows were changed or are incomplete. Upload the file again.',

	// ACP Logs
	'LOG_USER_ADDED'	=> '<strong>New user created</strong><br>» %s',
]);
