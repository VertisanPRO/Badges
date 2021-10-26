<?php
/*
 *  Made by Samerton
 *  https://github.com/NamelessMC/Nameless/tree/v2/
 *  NamelessMC version 2.0.0-pr9
 *
 *  License: MIT
 *
 *  Badges By xGIGABAITx & Mubeen
 */

$BadgesLanguage = $GLOBALS['BadgesLanguage'];
$page_title = $BadgesLanguage->get('general', 'title');

if ($user->isLoggedIn()) {
	if ($user->canViewStaffCP) {

		Redirect::to(URL::build('/'));
		die();
	}
	if (!$user->isAdmLoggedIn()) {

		Redirect::to(URL::build('/panel/auth'));
		die();
	} else {
		if (!$user->hasPermission('admincp.badges')) {
			require_once(ROOT_PATH . '/403.php');
			die();
		}
	}
} else {
	// Not logged in
	Redirect::to(URL::build('/login'));
	die();
}

define('PAGE', 'panel');
define('PARENT_PAGE', 'badges_items');
define('PANEL_PAGE', 'badges_items');

require_once(ROOT_PATH . '/core/templates/backend_init.php');


$badges_data = $queries->getWhere('badges_data', array('id', '<>', 0));
if (count($badges_data)) {

	foreach ($badges_data as $bg) {
		$badges_list[] = array(
			'edit_link' => URL::build('/panel/badges/new', 'action=edit&id=' . Output::getClean($bg->id)),
			'delete_link' => URL::build('/panel/badges', 'action=delete&id=' . Output::getClean($bg->id)),
			'id' => $bg->id,
			'name' => $bg->name,
			'require_posts' => $bg->require_posts
		);
	}
	$smarty->assign(array(
		'BADGES_LIST' => $badges_list
	));
}

$smarty->assign(array(
	'SUBMIT' => $language->get('general', 'submit'),
	'YES' => $language->get('general', 'yes'),
	'NO' => $language->get('general', 'no'),
	'BACK' => $language->get('general', 'back'),
	'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
	'CONFIRM_DELETE' => $language->get('general', 'confirm_delete'),
	'TITLE' => $BadgesLanguage->get('general', 'title'),
	'NAME' => $language->get('admin', 'name'),
	'DESCRIPTION' => $language->get('admin', 'description'),
	'NEW_BADGES' => $BadgesLanguage->get('general', 'new_badges'),
	'REQUIRE_POST' => $BadgesLanguage->get('general', 'require_posts'),
	'BADGES_URL' => $BadgesLanguage->get('general', 'badges_url'),
	'POSTS' => $BadgesLanguage->get('general', 'posts'),
	'NEW_BDG_LINK' => URL::build('/panel/badges/new')
));


$template_file = 'badges/badges.tpl';


if (!isset($_GET['action'])) {

	// ADD BADGES
	if (Input::exists()) {
		$errors = array();
		if (Token::check(Input::get('token'))) {

			$validate = new Validate();
			$validation = $validate->check($_POST, array(
				'require_posts' => array(
					'required' => true
				),
				'name' => array(
					'required' => true
				),
				'badges_url' => array(
					'required' => true
				)
			));

			if ($validation->passed()) {
				try {

					$queries->create('badges_data', array(
						'name' => Input::get('name'),
						'description' => Input::get('description'),
						'require_posts' => Input::get('require_posts'),
						'icon' => Input::get('badges_url')
					));

					Session::flash('staff', $BadgesLanguage->get('general', 'badget_created_successfully'));
					Redirect::to(URL::build('/panel/badges'));
				} catch (Exception $e) {
					$errors[] = $e->getMessage();
				}
			} else {
				$errors[] = $BadgesLanguage->get('general', 'add_errors');
			}
		} else {
			$errors[] = $language->get('general', 'invalid_token');
		}
	}
} else {
	switch ($_GET['action']) {

		case 'delete':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				try {

					$queries->delete('badges_data', array('id', '=', $_GET['id']));
				} catch (Exception $e) {
					die($e->getMessage());
				}

				Session::flash('staff');
				Redirect::to(URL::build('/panel/badges'));
				die();
			}
			break;

		case 'edit':

			if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
				Redirect::to(URL::build('/panel/badges'));
				die();
			}
			$edit_badges = $queries->getWhere('badges_data', array('id', '=', $_GET['id']));
			if (!count($edit_badges)) {
				Redirect::to(URL::build('/panel/badges'));
				die();
			}

			$edit_badges = $edit_badges[0];

			if (Input::exists()) {
				$errors = array();
				if (Token::check(Input::get('token'))) {

					$validate = new Validate();
					$validation = $validate->check($_POST, array(
						'require_posts' => array(
							'required' => true
						),
						'name' => array(
							'required' => true
						),
						'badges_url' => array(
							'required' => true
						)
					));

					if ($validation->passed()) {
						try {

							$queries->update('badges_data', $edit_badges->id, array(
								'name' => Input::get('name'),
								'description' => Input::get('description'),
								'require_posts' => Input::get('require_posts'),
								'icon' => Input::get('badges_url')
							));


							Session::flash('staff', $BadgesLanguage->get('general', 'badget_updated_successfully'));
							Redirect::to(URL::build('/panel/badges'));
							die();
						} catch (Exception $e) {
							$errors[] = $e->getMessage();
						}
					} else {
						$errors[] = $BadgesLanguage->get('general', 'edit_errors');
					}
				} else {
					$errors[] = $language->get('general', 'invalid_token');
				}
			}

			$smarty->assign(array(
				'EDIT_NAME' => Output::getClean($edit_badges->name),
				'EDIT_DESCRIPTION' => Output::getClean($edit_badges->description),
				'EDIT_REQUIRE_POST' => Output::getClean($edit_badges->require_posts),
				'EDIT_BADGET_URL' => Output::getClean($edit_badges->icon),
				'BACK_LINK' => URL::build('/panel/badges')
			));


			$template_file = 'badges/edit_badges.tpl';

			break;

		default:
			Redirect::to(URL::build('/panel/badges'));
			die();
			break;
	}
}


// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, array($navigation, $cc_nav, $mod_nav), $widgets, $template);
$page_load = microtime(true) - $start;
define('PAGE_LOAD_TIME', str_replace('{x}', round($page_load, 3), $language->get('general', 'page_loaded_in')));
$template->onPageLoad();

if (Session::exists('staff'))
	$success = Session::flash('staff');

if (isset($success))
	$smarty->assign(array(
		'SUCCESS' => $success,
		'SUCCESS_TITLE' => $language->get('general', 'success')
	));

if (isset($errors) && count($errors))
	$smarty->assign(array(
		'ERRORS' => $errors,
		'ERRORS_TITLE' => $language->get('general', 'error')
	));

$smarty->assign(array(
	'TOKEN' => Token::get(),
));

require(ROOT_PATH . '/core/templates/panel_navbar.php');

$template->displayTemplate($template_file, $smarty);
