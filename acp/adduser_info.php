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
 * Add User ACP module info
 */
class adduser_info
{
	public function module()
	{
		return [
			'filename'	=> '\phpbbmodders\adduser\acp\adduser_module',
			'title'		=> 'ADDUSER_ADD_USER',
			'modes'		=> [
				'main'	=> [
					'title'	=> 'ACP_ADD_USER',
					'auth'	=> 'ext_phpbbmodders/adduser && acl_a_user',
					'cat'	=> ['ACP_CAT_USERS'],
				],
				'csv'	=> [
					'title'	=> 'ACP_ADDUSER_CSV',
					'auth'	=> 'ext_phpbbmodders/adduser && acl_a_user',
					'cat'	=> ['ACP_CAT_USERS'],
				],
			],
		];
	}
}
