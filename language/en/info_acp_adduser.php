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
	'DIR_NOT_EXIST'					=> 'The language you have chosen %s does not have the files needed for the extension. Please translate them and upload to the %s directory of the extension.',
	'ACP_ACCOUNT_ADDED'				=> 'The user account has been created. A link was sent to the email address you provided; the user sets their own password with it. The email states how long the link is valid.',
	'ACP_ACCOUNT_INACTIVE'			=> 'The user account has been created. However, the forum settings require the user to activate their account.<br>An activation key has been sent to the email address you provided for the user. After activating, the user sets their own password with “Forgot password”.',
	'ACP_ACCOUNT_INACTIVE_ADMIN'	=> 'The user account has been created. However, the forum settings require account activation by an administrator.<br>An email has been sent to the Administrators and the user will be informed when their account has been activated',
	'ACP_ADMIN_ACTIVATE'			=> 'An email will be dispatched to an Administrator for account activation. Alternatively you may check the activate account box below to activate the account instantly once created. The user will receive a link to set their own password.',
	'ACP_EMAIL_ACTIVATE'			=> 'Once the account has been created, the user will receive an email containing an activation link to activate the account. After activating, they set their own password with “Forgot password”.',
	'ACP_INSTANT_ACTIVATE'			=> 'The account will be activated instantly. The user will receive a link to set their own password.',

	'ADD_USER'					=> 'Add User',
	'ADD_USER_EXPLAIN'			=> 'Create a new user account. If your activation settings are to Admin Activation only, you will have the option to activate the user instantly.',
	'ADMIN_ACTIVATE'			=> 'Activate user account',
	'CONFIRM_PASSWORD'			=> 'Confirm password',
	'EDIT_USER_GROUPS'			=> '%sClick here to edit the user groups for this user%s',
	'GROUP_ADD'					=> 'Making a selection here will add the user to the group selected as well as to the registered users group.',
	'GROUP_DEFAULT_EXPLAIN'		=> 'Checking this box will set the group selected above to be the users default.',
	'CONTINUE_EDIT_USER'		=> '%1$sClick here to manage %2$s’s profile%3$s',
	'PASSWORD_EXPLAIN'			=> 'If left blank a password will be generated. It is never emailed — the user sets their own password with the link.',
	'ENABLE_NEWUSER'			=> 'Enable new user',
	'ENABLE_NEWUSER_EXPLAIN'	=> 'If set as yes the user will be added to the newly registered user group',

	'GROUP_NOT_ALLOWED'			=> 'New accounts cannot be added to the selected group.',

	// Add users from CSV
	'ACP_ADD_USERS_CSV'			=> 'Add users from CSV',
	'ADD_USERS_CSV'				=> 'Add users from CSV',
	'ADD_USERS_CSV_EXPLAIN'		=> 'Create many user accounts at once from a CSV file. Every account is checked and created the same way as on the Add User page. You see every row and its errors before anything is created. Custom profile fields are not set; edit the users afterwards if they need them.',
	'CSV_FILE'					=> 'CSV file',
	'CSV_FILE_EXPLAIN'			=> 'One user per row, UTF-8, with commas or semicolons between the fields. The first row names the columns, in any order: <strong>username</strong> and <strong>email</strong> are required, <strong>password</strong>, <strong>lang</strong> (a language code such as en) and <strong>birthday</strong> (DD-MM-YYYY) are optional. A blank password gets a random one that is never emailed. At most %1$d rows and %2$s per file. To use an Excel or other spreadsheet, save it as “CSV UTF-8” first.',
	'CSV_OPTIONS'				=> 'Options for every account',
	'CSV_CHECK_ROWS'			=> 'Check rows',
	'CSV_CREATE_ACCOUNTS'		=> [
		1	=> 'Create %d account',
		2	=> 'Create %d accounts',
	],
	'CSV_PREVIEW'				=> 'Check the rows',
	'CSV_PREVIEW_EXPLAIN'		=> 'Nothing has been created yet. Rows with errors are skipped; to add those users, correct the rows and upload them again afterwards.',
	'CSV_READY_COUNT'			=> [
		0	=> 'No rows are ready.',
		1	=> '%d row is ready.',
		2	=> '%d rows are ready.',
	],
	'CSV_SKIPPED_COUNT'			=> [
		0	=> 'No rows are skipped.',
		1	=> '%d row is skipped.',
		2	=> '%d rows are skipped.',
	],
	'CSV_CREATED_COUNT'			=> [
		0	=> 'No accounts were created.',
		1	=> '%d account was created.',
		2	=> '%d accounts were created.',
	],
	'CSV_SUMMARY'				=> 'Accounts created',
	'CSV_CREATED'				=> 'Created',
	'CSV_SKIPPED'				=> 'Skipped',
	'CSV_ROW'					=> 'Row',
	'CSV_STATUS'				=> 'Status',
	'CSV_READY'					=> 'Ready',
	'CSV_PASSWORD_GIVEN'		=> 'From the file',
	'CSV_PASSWORD_GENERATED'	=> 'Generated',
	'CSV_UPLOAD_ANOTHER'		=> 'Upload another file',
	'CSV_BIRTHDAYS_IGNORED'		=> 'Birthdays are disabled on this board, so the birthday column is ignored.',

	'CSV_NO_FILE'				=> 'Choose a CSV file to upload.',
	'CSV_TOO_LARGE'				=> 'The file is larger than %s.',
	'CSV_UPLOAD_FAILED'			=> 'The file could not be uploaded.',
	'CSV_EMPTY'					=> 'The file is empty.',
	'CSV_NOT_UTF8'				=> 'The file is not UTF-8 text. Save it as “CSV UTF-8” and upload it again.',
	'CSV_UNKNOWN_COLUMN'		=> 'The first row names a column “%1$s”. It must name the columns, using only: %2$s.',
	'CSV_DUPLICATE_COLUMN'		=> 'The first row names the column “%s” more than once.',
	'CSV_MISSING_COLUMN'		=> 'The first row has no “%s” column. It must name the columns, and username and email are required.',
	'CSV_TOO_MANY_ROWS'			=> 'The file has more than %d rows. Split it into smaller files.',
	'CSV_NO_ROWS'				=> 'The file has no users below the first row.',
	'CSV_TOO_MANY_FIELDS'		=> 'This row has more fields than the %d columns named in the first row.',
	'CSV_BIRTHDAY_FORMAT'		=> 'The birthday must be written as DD-MM-YYYY.',
	'CSV_DUPLICATE_USERNAME'	=> 'The same username is on row %d.',
	'CSV_DUPLICATE_EMAIL'		=> 'The same email address is on row %d.',
	'CSV_LANG_NOT_AVAILABLE'	=> 'The Add User emails are not available in the language “%s”.',
	'CSV_DATA_INVALID'			=> 'The checked rows were changed or are incomplete. Upload the file again.',

	// ACP Logs
	'LOG_USER_ADDED'	=> '<strong>New user created</strong><br>» %s',
]);
