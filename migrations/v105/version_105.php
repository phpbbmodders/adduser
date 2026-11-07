<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser\migrations\v105;

/**
 * Adds the "Add users from CSV" ACP page.
 */
class version_105 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\phpbbmodders\adduser\migrations\v104\version_104'];
	}

	public function update_data()
	{
		return [
			['module.add', [
				'acp',
				'ACP_CAT_USERS',
				[
					'module_basename'	=> '\phpbbmodders\adduser\acp\adduser_module',
					'modes'				=> ['csv'],
				],
			]],
		];
	}

	public function revert_data()
	{
		return [
			['module.remove', [
				'acp',
				'ACP_CAT_USERS',
				[
					'module_basename'	=> '\phpbbmodders\adduser\acp\adduser_module',
					'modes'				=> ['csv'],
				],
			]],
		];
	}
}
