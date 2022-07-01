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


$badges_data = DB::getInstance()->get('badges_data', ['id', '<>', 0])->results();
$badges_list = [];
if (count($badges_data)) {
    foreach ($badges_data as $bg) {
        $badges_list[] = [
            'edit_link' => URL::build('/panel/badges/new', 'action=edit&id=' . Output::getClean($bg->id)),
            'delete_link' => URL::build('/panel/badges', 'action=delete&id=' . Output::getClean($bg->id)),
            'id' => $bg->id,
            'name' => $bg->name,
            'require_posts' => $bg->require_posts
        ];
    }
}

$smarty->assign([
    'BADGES_LIST' => $badges_list
]);

$smarty->assign([
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
]);


$template_file = 'badges/badges.tpl';


if (!isset($_GET['action'])) {

    // ADD BADGES
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
                    ],
                    'badges_url' => [
                        'required' => true
                    ]
                ]);

                if ($validation->passed()) {
                    try {

                        DB::getInstance()->insert('badges_data', [
                            'name' => Input::get('name'),
                            'description' => Input::get('description'),
                            'require_posts' => Input::get('require_posts'),
                            'icon' => Input::get('badges_url')
                        ]);

                        Session::flash('staff', $BadgesLanguage->get('general', 'badge_created_successfully'));
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
        } catch (Exception $e) {
            // Error
        }
    }
} else {
    switch ($_GET['action']) {

        case 'delete':
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                try {

                    DB::getInstance()->delete('badges_data', ['id', '=', $_GET['id']]);
                } catch (Exception $e) {
                    die($e->getMessage());
                }

                Session::flash('staff');
                Redirect::to(URL::build('/panel/badges'));
            }
            break;

        case 'edit':

            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                Redirect::to(URL::build('/panel/badges'));
            }
            $edit_badges = DB::getInstance()->get('badges_data', ['id', '=', $_GET['id']])->results();
            if (!count($edit_badges)) {
                Redirect::to(URL::build('/panel/badges'));
            }

            $edit_badges = $edit_badges[0];

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
                            ],
                            'badges_url' => [
                                'required' => true
                            ]
                        ]);

                        if ($validation->passed()) {
                            try {

                                DB::getInstance()->update('badges_data', $edit_badges->id, [
                                    'name' => Input::get('name'),
                                    'description' => Input::get('description'),
                                    'require_posts' => Input::get('require_posts'),
                                    'icon' => Input::get('badges_url')
                                ]);


                                Session::flash('staff', $BadgesLanguage->get('general', 'badge_updated_successfully'));
                                Redirect::to(URL::build('/panel/badges'));
                            } catch (Exception $e) {
                                $errors[] = $e->getMessage();
                            }
                        } else {
                            $errors[] = $BadgesLanguage->get('general', 'edit_errors');
                        }
                    } else {
                        $errors[] = $language->get('general', 'invalid_token');
                    }
                } catch (Exception $e) {
                    // Error
                }
            }

            $smarty->assign([
                'EDIT_NAME' => Output::getClean($edit_badges->name),
                'EDIT_DESCRIPTION' => Output::getClean($edit_badges->description),
                'EDIT_REQUIRE_POST' => Output::getClean($edit_badges->require_posts),
                'EDIT_BADGE_URL' => Output::getClean($edit_badges->icon),
                'BACK_LINK' => URL::build('/panel/badges')
            ]);


            $template_file = 'badges/edit_badges.tpl';

            break;

        default:
            Redirect::to(URL::build('/panel/badges'));
    }
}


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
