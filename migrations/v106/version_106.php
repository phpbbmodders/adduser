<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser\migrations\v106;

/**
 * Adds the setting for an administrator copy of welcome emails, off by default.
 */
class version_106 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['adduser_admin_copy']);
	}

	public static function depends_on()
	{
		return ['\phpbbmodders\adduser\migrations\v105\version_105'];
	}

	public function update_data()
	{
		return [
			['config.add', ['adduser_admin_copy', 0]],
		];
	}
}
