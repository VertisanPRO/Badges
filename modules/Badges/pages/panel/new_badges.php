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


$smarty->assign(array(
	'SUBMIT' => $language->get('general', 'submit'),
	'YES' => $language->get('general', 'yes'),
	'NO' => $language->get('general', 'no'),
	'BACK' => $language->get('general', 'back'),
	'BACK_LINK' => URL::build('/panel/badges'),
	'ARE_YOU_SURE' => $language->get('general', 'are_you_sure'),
	'CONFIRM_DELETE' => $language->get('general', 'confirm_delete'),
	'TITLE' => $BadgesLanguage->get('general', 'title'),
	'NAME' => $language->get('admin', 'name'),
	'DESCRIPTION' => $language->get('admin', 'description'),
	'NEW_BADGES' => $BadgesLanguage->get('general', 'new_badges'),
	'REQUIRE_POST' => $BadgesLanguage->get('general', 'require_posts'),
	'BADGES_URL' => $BadgesLanguage->get('general', 'badges_url'),
	'POSTS' => $BadgesLanguage->get('general', 'posts'),
	'BDG_COLOR_TITLE' => $BadgesLanguage->get('general', 'bdg_color'),
	'BDG_INON_TITLE' => $BadgesLanguage->get('general', 'bdg_icon'),
	'BDG_RIBBON_TITLE' => $BadgesLanguage->get('general', 'bdg_text'),
));


if (isset($_GET['action'])) {

	// EDIT BADGES

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

	$smarty->assign(array(
		'EDIT_NAME' => Output::getClean($edit_badges->name),
		'EDIT_REQUIRE_POST' => Output::getClean($edit_badges->require_posts),
		'EDIT_BDG_COLOR' => Output::getClean($edit_badges->bdg_color),
		'EDIT_BDG_ICON' => Output::getClean($edit_badges->bdg_icon),
		'SET_EDIT_BDG_ICON' => $edit_badges->bdg_icon,
		'EDIT_BDG_RIBBON' => Output::getClean($edit_badges->bdg_ribbon)
	));


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
				)
			));

			if ($validation->passed()) {
				try {

					$queries->update('badges_data', $edit_badges->id, array(
						'name' => Input::get('name'),
						'require_posts' => Input::get('require_posts'),
						'bdg_color' => Input::get('bdg_color'),
						'bdg_icon' => Input::get('bdg_icon'),
						'bdg_ribbon' => Input::get('bdg_ribbon')
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
				)
			));

			if ($validation->passed()) {
				try {

					$queries->create('badges_data', array(
						'name' => Input::get('name'),
						'require_posts' => Input::get('require_posts'),
						'bdg_color' => Input::get('bdg_color'),
						'bdg_icon' => Input::get('bdg_icon'),
						'bdg_ribbon' => Input::get('bdg_ribbon')
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
}

$template_file = 'badges/new_badges.tpl';
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
