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


$queries = new Queries();
$user_id = explode('/', $_REQUEST['route']);
$user_id = $queries->getWhere('users', array('username', '=', $user_id['2']));
$user_id = $user_id['0']->id;

$user_posts = count($queries->getWhere('topics', array('topic_creator', '=', $user_id)));

$badges_data = $queries->getWhere('badges_data', array('id', '<>', 0));

if (count($badges_data)) {
	foreach ($badges_data as $value) {
		if ($user_posts >= $value->require_posts) {
			$badges_list[] = array(
				'status' => 1,
				'name' => $value->name,
				'require_posts' => $value->require_posts,
				'bdg_color' => $value->bdg_color,
				'bdg_icon' => $value->bdg_icon,
				'bdg_ribbon' => $value->bdg_ribbon
			);
		} else {
			$badges_list[] = array(
				'status' => 0,
				'name' => $value->name,
				'require_posts' => $value->require_posts,
				'bdg_color' => $value->bdg_color,
				'bdg_icon' => $value->bdg_icon,
				'bdg_ribbon' => $value->bdg_ribbon
			);
		}
	}
	$smarty->assign(array(
		'BADGES_LIST' => $badges_list,
		'USER_POSTS' => $user_posts,
		'POSTS' => $BadgesLanguage->get('general', 'posts'),
	));
}
