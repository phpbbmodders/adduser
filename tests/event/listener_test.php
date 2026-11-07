<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser\tests\event;

use phpbbmodders\adduser\event\listener;

/**
 * Tests \phpbbmodders\adduser\event\listener
 */
class listener_test extends \phpbb_test_case
{
	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		if (!function_exists('phpbb_insert_config_array'))
		{
			include($phpbb_root_path . 'includes/functions_acp.' . $phpEx);
		}
	}

	public function test_subscribes_to_the_board_settings_event()
	{
		$this->assertSame(['core.acp_board_config_edit_add'], array_keys(listener::getSubscribedEvents()));
	}

	/**
	 * Board settings shaped like the start of phpBB's User registration page
	 *
	 * @param string $mode Board settings mode
	 * @return \phpbb\event\data
	 */
	protected function board_settings_event($mode)
	{
		return new \phpbb\event\data([
			'mode'			=> $mode,
			'submit'		=> false,
			'display_vars'	=> [
				'title'	=> 'ACP_REGISTER_SETTINGS',
				'vars'	=> [
					'legend1'					=> 'GENERAL_SETTINGS',
					'new_member_post_limit'		=> ['lang' => 'NEW_MEMBER_POST_LIMIT', 'validate' => 'int:0:255', 'type' => false, 'explain' => true],
					'new_member_group_default'	=> ['lang' => 'NEW_MEMBER_GROUP_DEFAULT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
					'min_name_chars'			=> ['lang' => 'USERNAME_LENGTH', 'validate' => 'int:1', 'type' => false, 'explain' => true],
				],
			],
		]);
	}

	public function test_adds_the_setting_after_the_newly_registered_default()
	{
		$event = $this->board_settings_event('registration');

		(new listener())->add_admin_copy_setting($event);

		$this->assertSame(
			['legend1', 'new_member_post_limit', 'new_member_group_default', 'adduser_admin_copy', 'min_name_chars'],
			array_keys($event['display_vars']['vars'])
		);
		$this->assertSame('bool', $event['display_vars']['vars']['adduser_admin_copy']['validate']);
	}

	public function test_leaves_other_board_settings_pages_alone()
	{
		$event = $this->board_settings_event('settings');
		$before = $event['display_vars'];

		(new listener())->add_admin_copy_setting($event);

		$this->assertSame($before, $event['display_vars']);
	}
}
