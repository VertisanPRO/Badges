<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/tree/v2/
 *  NamelessMC version 2.0.0-pr9
 *
 *  License: MIT
 *
 *  Badges By xGIGABAITx & Mubeen
 */

class Badges extends Module
{

	private $_language, $BadgesLanguage;

	public function __construct($language, $pages, $INFO_MODULE)
	{
		$this->_language = $language;

		$this->BadgesLanguage = $GLOBALS['BadgesLanguage'];

		$this->module_name = $INFO_MODULE['name'];
		$author = $INFO_MODULE['author'];
		$module_version = $INFO_MODULE['module_ver'];
		$nameless_version = $INFO_MODULE['nml_ver'];
		parent::__construct($this, $this->module_name, $author, $module_version, $nameless_version);

		// StaffCP
		$pages->add($this->module_name, '/panel/badges', 'pages/panel/badges.php');
		$pages->add($this->module_name, '/panel/badges/new', 'pages/panel/new_badges.php');
	}

	public function onInstall()
	{

		try {
			$engine = Config::get('mysql/engine');
			$charset = Config::get('mysql/charset');
		} catch (Exception $e) {
			$engine = 'InnoDB';
			$charset = 'utf8mb4';
		}
		if (!$engine || is_array($engine))
			$engine = 'InnoDB';

		if (!$charset || is_array($charset))
			$charset = 'latin1';

		// Queries
		$queries = new Queries();

		try {
			$queries->createTable("badges_data", "`id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `require_posts` int(11) NOT NULL, `bdg_color` varchar(255) DEFAULT NULL, `bdg_icon` varchar(255) DEFAULT NULL, `bdg_ribbon` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`)", "ENGINE=$engine DEFAULT CHARSET=$charset");
		} catch (Exception $e) {
			// Error
		}
		try {
			$queries->createTable("badges_users_data", "`id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `badges_id` int(11) NOT NULL, PRIMARY KEY (`id`)", "ENGINE=$engine DEFAULT CHARSET=$charset");
		} catch (Exception $e) {
			// Error
		}
	}

	public function onUninstall()
	{
	}

	public function onEnable()
	{

		$queries = new Queries();

		try {

			$group = $queries->getWhere('groups', array('id', '=', 2));
			$group = $group[0];

			$group_permissions = json_decode($group->permissions, TRUE);
			$group_permissions['admincp.badges'] = 1;

			$group_permissions = json_encode($group_permissions);
			$queries->update('groups', 2, array('permissions' => $group_permissions));
		} catch (Exception $e) {
			// Ошибка
		}
	}

	public function onDisable()
	{
	}

	public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template)
	{

		PermissionHandler::registerPermissions($this->module_name, array(
			'admincp.badges' => $this->BadgesLanguage->get('general', 'group_permision')
		));

		$icon = '<i class="nav-icon fas fa-ribbon"></i>';
		$order = 15;

		if (defined('FRONT_END')) {

			$queries = new Queries();

			$badges_data = $queries->getWhere('badges_data', array('id', '<>', 0));

			if (count($badges_data)) {

				$badges_user_data = $queries->getWhere('badges_users_data', array('user_id', '=', $user->data()->id));
				foreach ($badges_user_data as $value) {
					$user_badges_list[$value->badges_id] = array(
						'user_id' => $value->user_id
					);
				}

				$user_posts = count($queries->getWhere('topics', array('topic_creator', '=', $user->data()->id)));
				foreach ($badges_data as $value) {

					$badges_list[$value->id] = array(
						'name' => $value->name,
						'bdg_color' => $value->bdg_color,
						'bdg_icon' => $value->bdg_icon,
						'bdg_ribbon' => $value->bdg_ribbon
					);

					if ($user_posts >= $value->require_posts) {
						if (isset($user_badges_list[$value->id])) {
							continue;
						} else {
							$queries->create('badges_users_data', array(
								'user_id' => $user->data()->id,
								'badges_id' => $value->id
							));
						}
					}
				}

				$users_data = $queries->getWhere('badges_users_data', array('id', '<>', 0));
				foreach ($users_data as $value) {
					if (isset($badges_list[$value->badges_id])) {
						$user_data_array[] = array(
							'user_id' => $value->user_id,
							'name' => $badges_list[$value->badges_id]['name'],
							'bdg_color' => $badges_list[$value->badges_id]['bdg_color'],
							'bdg_icon' => $badges_list[$value->badges_id]['bdg_icon'],
							'bdg_ribbon' => $badges_list[$value->badges_id]['bdg_ribbon'],

						);
					}

					// User count badges
					$user_data_count[$value->user_id] = array(
						'count' => count($queries->getWhere('badges_users_data', array('user_id', '=', $value->user_id)))
					);
					$smarty->assign(array(
						'USER_BDG_COUNT' => $user_data_count
					));
					// User count badges \\
				}

				$smarty->assign(array(
					'USER_BADGES_LIST' => $user_data_array
				));
			}

			$smarty->assign(array(
				'BDG_TITLE' => $this->BadgesLanguage->get('general', 'title')
			));
		}

		if (defined('BACK_END')) {

			$title = $this->BadgesLanguage->get('general', 'title');


			if ($user->hasPermission('admincp.badges')) {

				$navs[2]->add('badges_divider', mb_strtoupper($title, 'UTF-8'), 'divider', 'top', null, $order, '');

				$navs[2]->add('badges_items', $title, URL::build('/panel/badges'), 'top', null, $order + 0.1, $icon);
			}
		}
	}
}
