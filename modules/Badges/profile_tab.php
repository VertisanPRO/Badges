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
$user_id = DB::getInstance()->get('users', ['username', '=', $user_id['2']])->results();
$user_id = $user_id['0']->id;

$user_posts = count(DB::getInstance()->get('topics', ['topic_creator', '=', $user_id])->results());

$badges_data = DB::getInstance()->get('badges_data', ['id', '<>', 0])->results();

if (count($badges_data)) {
	foreach ($badges_data as $value) {
		if ($user_posts >= $value->require_posts) {
			$badges_list[] = [
				'status' => 1,
				'name' => $value->name,
				'require_posts' => $value->require_posts,
				'bdg_color' => $value->bdg_color,
				'bdg_icon' => $value->bdg_icon,
				'bdg_ribbon' => $value->bdg_ribbon
            ];
		} else {
			$badges_list[] = [
				'status' => 0,
				'name' => $value->name,
				'require_posts' => $value->require_posts,
				'bdg_color' => $value->bdg_color,
				'bdg_icon' => $value->bdg_icon,
				'bdg_ribbon' => $value->bdg_ribbon
            ];
		}
	}
	$smarty->assign([
		'BADGES_LIST' => $badges_list,
		'USER_POSTS' => $user_posts,
		'POSTS' => $BadgesLanguage->get('general', 'posts'),
    ]);
}
