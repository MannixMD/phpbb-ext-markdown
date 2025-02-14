<?php

/**
 * Markdown extension for phpBB.
 * @author Alfredo Ramos <alfredo.ramos@protonmail.com>
 * @copyright 2019 Alfredo Ramos
 * @license GPL-2.0-only
 */

namespace alfredoramos\markdown\tests\event;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\user;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\routing\helper as routing_helper;
use phpbb\language\language;
use alfredoramos\markdown\includes\helper;
use alfredoramos\markdown\event\listener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @group event
 */
class listener_test extends \phpbb_test_case
{
	protected $auth;
	protected $config;
	protected $user;
	protected $request;
	protected $template;
	protected $routing_helper;
	protected $language;
	protected $helper;

	protected function setUp(): void
	{
		parent::setUp();
		$this->auth = $this->getMockBuilder(auth::class)->getMock();
		$this->config = $this->getMockBuilder(config::class)
			->disableOriginalConstructor()->getMock();
		$this->user = $this->getMockBuilder(user::class)
			->disableOriginalConstructor()->getMock();
		$this->request = $this->getMockBuilder(request::class)
		   ->disableOriginalConstructor()->getMock();
		$this->template = $this->getMockBuilder(template::class)->getMock();
		$this->routing_helper = $this->getMockBuilder(routing_helper::class)
		   ->disableOriginalConstructor()->getMock();
		$this->language = $this->getMockBuilder(language::class)
		   ->disableOriginalConstructor()->getMock();
		$this->helper = $this->getMockBuilder(helper::class)
			->disableOriginalConstructor()->getMock();
	}

	public function test_instance()
	{
		$this->assertInstanceOf(
			EventSubscriberInterface::class,
			new listener(
				$this->auth,
				$this->config,
				$this->user,
				$this->request,
				$this->template,
				$this->routing_helper,
				$this->language,
				$this->helper
			)
		);
	}

	public function test_subscribed_events()
	{
		$this->assertSame(
			[
				'core.user_setup',
				'core.acp_board_config_edit_add',
				'core.permissions',
				'core.text_formatter_s9e_configure_after',
				'core.text_formatter_s9e_parser_setup',
				'core.ucp_display_module_before',
				'core.ucp_prefs_post_data',
				'core.ucp_prefs_post_update_data',
				'core.posting_modify_default_variables',
				'core.posting_modify_message_text',
				'core.posting_modify_submit_post_before',
				'core.submit_post_modify_sql_data',
				'core.posting_modify_template_vars',
				'core.ucp_pm_compose_modify_parse_before',
				'core.message_parser_check_message'
			],
			array_keys(listener::getSubscribedEvents())
		);
	}
}
