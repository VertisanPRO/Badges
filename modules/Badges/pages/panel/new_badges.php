<?php
/*
 *  Made by Samerton
 *  https://github.com/NamelessMC/Nameless/tree/v2/
 *  NamelessMC version 2.1.0
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
    }
    if (!$user->isAdmLoggedIn()) {

        Redirect::to(URL::build('/panel/auth'));
    } else {
        if (!$user->hasPermission('admincp.badges')) {
            require_once(ROOT_PATH . '/403.php');
        }
    }
} else {
    // Not logged in
    Redirect::to(URL::build('/login'));
}

const PAGE = 'panel';
const PARENT_PAGE = 'badges_items';
const PANEL_PAGE = 'badges_items';

require_once(ROOT_PATH . '/core/templates/backend_init.php');

$smarty->assign([
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
]);

if (isset($_GET['action'])) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        Redirect::to(URL::build('/panel/badges'));
    }
    $edit_badges = DB::getInstance()->get('badges_data', ['id', '=', $_GET['id']])->results();
    if (!count($edit_badges)) {
        Redirect::to(URL::build('/panel/badges'));
    }
    $edit_badges = $edit_badges[0];
    $smarty->assign([
        'EDIT_NAME' => Output::getClean($edit_badges->name),
        'EDIT_REQUIRE_POST' => Output::getClean($edit_badges->require_posts),
        'EDIT_BDG_COLOR' => Output::getClean($edit_badges->bdg_color),
        'EDIT_BDG_ICON' => Output::getClean($edit_badges->bdg_icon),
        'SET_EDIT_BDG_ICON' => $edit_badges->bdg_icon,
        'EDIT_BDG_RIBBON' => Output::getClean($edit_badges->bdg_ribbon)
    ]);
    if (Input::exists()) {
        $errors = [];
        try {
            if (Token::check(Input::get('token'))) {
                $validation = Validate::check($_POST, [
                    'require_posts' => [
                        'required' => true
                    ],
                    'name' => [
                        'required' => true
                    ]
                ]);
                if ($validation->passed()) {
                    try {
                        DB::getInstance()->update('badges_data', $edit_badges->id, [
                            'name' => Input::get('name'),
                            'require_posts' => Input::get('require_posts'),
                            'bdg_color' => Input::get('bdg_color'),
                            'bdg_icon' => Input::get('bdg_icon'),
                            'bdg_ribbon' => Input::get('bdg_ribbon')
                        ]);
                        Session::flash('staff', $BadgesLanguage->get('general', 'badge_created_successfully'));
                        Redirect::to(URL::build('/panel/badges'));
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                } else {
                    $errors[] = $BadgesLanguage->get('general', 'error');
                }
            } else {
                $errors[] = $language->get('general', 'invalid_token');
            }
        } catch (Exception $e) {
            // Error
        }
    }
} else {
    if (Input::exists()) {
        $errors = [];
        try {
            if (Token::check(Input::get('token'))) {
                $validation = Validate::check($_POST, [
                    'require_posts' => [
                        'required' => true
                    ],
                    'name' => [
                        'required' => true
                    ]
                ]);
                if ($validation->passed()) {
                    try {
                        DB::getInstance()->insert('badges_data', [
                            'name' => Input::get('name'),
                            'require_posts' => Input::get('require_posts'),
                            'bdg_color' => Input::get('bdg_color'),
                            'bdg_icon' => Input::get('bdg_icon'),
                            'bdg_ribbon' => Input::get('bdg_ribbon')
                        ]);
                        Session::flash('staff', $BadgesLanguage->get('general', 'badge_created_successfully'));
                        Redirect::to(URL::build('/panel/badges'));
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                } else {
                    $errors[] = $BadgesLanguage->get('general', 'error');
                }
            } else {
                $errors[] = $language->get('general', 'invalid_token');
            }
        } catch (Exception $e) {
            // Error
        }
    }
}

$template_file = 'badges/new_badges.tpl';
// Load modules + template
Module::loadPage($user, $pages, $cache, $smarty, [$navigation, $cc_nav, $staffcp_nav], $widgets, $template);

$template->onPageLoad();

if (Session::exists('staff'))
    $success = Session::flash('staff');

if (isset($success))
    $smarty->assign([
        'SUCCESS' => $success,
        'SUCCESS_TITLE' => $language->get('general', 'success')
    ]);

if (isset($errors) && count($errors))
    $smarty->assign([
        'ERRORS' => $errors,
        'ERRORS_TITLE' => $language->get('general', 'error')
    ]);

$smarty->assign([
    'TOKEN' => Token::get(),
]);

require(ROOT_PATH . '/core/templates/panel_navbar.php');

$template->displayTemplate($template_file, $smarty);