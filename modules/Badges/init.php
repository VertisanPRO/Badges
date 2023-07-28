<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/tree/v2/
 *  NamelessMC version 2.1.0
 *
 *  License: MIT
 *
 *  Badges By xGIGABAITx & Mubeen
 */

$BadgesLanguage = new Language(ROOT_PATH . '/modules/' . 'Badges' . '/language', LANGUAGE);
$GLOBALS['BadgesLanguage'] = $BadgesLanguage;

if (!isset($profile_tabs))
    $profile_tabs = [];

$profile_tabs['Badges'] = [
    'title' => $BadgesLanguage->get('general', 'title'),
    'smarty_template' => 'badges/user/profile_tab.tpl',
    'require' => ROOT_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'Badges' . DIRECTORY_SEPARATOR . 'profile_tab.php'
];

require_once(ROOT_PATH . '/modules/' . 'Badges' . '/module.php');

$module = new Badges($language, $pages);