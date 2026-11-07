<?php
/**
 *
 * Add User extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\adduser\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Add User event listener
 */
class listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.acp_board_config_edit_add'	=> 'add_admin_copy_setting',
		];
	}

	/**
	 * Add the "Send a copy of Add User welcome emails" setting to ACP >
	 * Board settings > User registration settings, after "Set Newly
	 * registered users group to default". Its language strings are in
	 * info_acp_adduser.php, which phpBB loads on every ACP page.
	 *
	 * @param \phpbb\event\data $event core.acp_board_config_edit_add data
	 * @return void
	 */
	public function add_admin_copy_setting($event)
	{
		if ($event['mode'] !== 'registration')
		{
			return;
		}

		$display_vars = $event['display_vars'];
		$display_vars['vars'] = phpbb_insert_config_array($display_vars['vars'], [
			'adduser_admin_copy'	=> ['lang' => 'ADDUSER_ADMIN_COPY', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true],
		], ['after' => 'new_member_group_default']);
		$event['display_vars'] = $display_vars;
	}
}
