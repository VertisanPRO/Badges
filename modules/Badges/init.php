<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/tree/v2/
 *  NamelessMC version 2.0.0-pr10
 *
 *  License: MIT
 *
 *  Badges By xGIGABAITx & Mubeen
 */

$INFO_MODULE = [
    'name' => 'Badges',
    'author' => '<a href="https://github.com/GIGABAIT-Official" target="_blank" rel="nofollow noopener">VertisanPRO</a>',
    'module_ver' => '1.2.1',
    'nml_ver' => '2.0.0-pr13',
];

$BadgesLanguage = new Language(ROOT_PATH . '/modules/' . $INFO_MODULE['name'] . '/language', LANGUAGE);

$GLOBALS['BadgesLanguage'] = $BadgesLanguage;

if (!isset($profile_tabs)) $profile_tabs = [];

$profile_tabs['Badges'] = [
    'title' => $BadgesLanguage->get('general', 'user_page_title'),
    'smarty_template' => 'badges/user/profile_tab.tpl',
    'require' => ROOT_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $INFO_MODULE['name'] . DIRECTORY_SEPARATOR . 'profile_tab.php'
];

require_once(ROOT_PATH . '/modules/' . $INFO_MODULE['name'] . '/module.php');

$module = new Badges($language, $pages, $INFO_MODULE);
