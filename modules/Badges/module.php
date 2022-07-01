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
    private $_language;
    private $BadgesLanguage;
    private $module_name;

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
    }

    public function onUninstall()
    {
    }

    public function onEnable()
    {

        try {
            DB::getInstance()->createTable("badges_data", "`id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `require_posts` int(11) NOT NULL, `bdg_color` varchar(255) DEFAULT NULL, `bdg_icon` varchar(255) DEFAULT NULL, `bdg_ribbon` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`)");
            DB::getInstance()->createTable("badges_users_data", "`id` int(11) NOT NULL AUTO_INCREMENT, `user_id` int(11) NOT NULL, `badges_id` int(11) NOT NULL, PRIMARY KEY (`id`)");
            $group = DB::getInstance()->get('groups', ['id', '=', 2])->results();
            $group = $group[0];

            $group_permissions = json_decode($group->permissions, TRUE);
            $group_permissions['admincp.badges'] = 1;

            $group_permissions = json_encode($group_permissions);
            DB::getInstance()->update('groups', 2, ['permissions' => $group_permissions]);
        } catch (Exception $e) {
            // Error
        }
    }

    public function onDisable()
    {
    }

    public function onPageLoad($user, $pages, $cache, $smarty, $navs, $widgets, $template)
    {

        PermissionHandler::registerPermissions($this->module_name, [
            'admincp.badges' => $this->BadgesLanguage->get('general', 'group_permission')
        ]);

        $icon = '<i class="nav-icon fas fa-ribbon"></i>';
        $order = 15;

        if (defined('FRONT_END')) {

            $badges_data = DB::getInstance()->get('badges_data', ['id', '<>', 0])->results();

            if (count($badges_data)) {

                $badges_user_data = DB::getInstance()->get('badges_users_data', ['user_id', '=', $user->data()->id])->results();
                foreach ($badges_user_data as $value) {
                    $user_badges_list[$value->badges_id] = [
                        'user_id' => $value->user_id
                    ];
                }

                $user_posts = count(DB::getInstance()->get('topics', ['topic_creator', '=', $user->data()->id])->results());
                foreach ($badges_data as $value) {

                    $badges_list[$value->id] = [
                        'name' => $value->name,
                        'bdg_color' => $value->bdg_color,
                        'bdg_icon' => $value->bdg_icon,
                        'bdg_ribbon' => $value->bdg_ribbon
                    ];

                    if ($user_posts >= $value->require_posts) {
                        if (isset($user_badges_list[$value->id])) {
                            continue;
                        } else {
                            DB::getInstance()->insert('badges_users_data', [
                                'user_id' => $user->data()->id,
                                'badges_id' => $value->id
                            ]);
                        }
                    }
                }

                $users_data = DB::getInstance()->get('badges_users_data', ['id', '<>', 0])->results();
                foreach ($users_data as $value) {
                    if (isset($badges_list[$value->badges_id])) {
                        $user_data_array[] = [
                            'user_id' => $value->user_id,
                            'name' => $badges_list[$value->badges_id]['name'],
                            'bdg_color' => $badges_list[$value->badges_id]['bdg_color'],
                            'bdg_icon' => $badges_list[$value->badges_id]['bdg_icon'],
                            'bdg_ribbon' => $badges_list[$value->badges_id]['bdg_ribbon'],

                        ];
                    }

                    // User count badges
                    $user_data_count[$value->user_id] = [
                        'count' => count(DB::getInstance()->get('badges_users_data', ['user_id', '=', $value->user_id])->results())
                    ];
                    $smarty->assign([
                        'USER_BDG_COUNT' => $user_data_count
                    ]);
                    // User count badges \\
                }

                $smarty->assign([
                    'USER_BADGES_LIST' => $user_data_array
                ]);
            }

            $smarty->assign([
                'BDG_TITLE' => $this->BadgesLanguage->get('general', 'title')
            ]);
        }

        if (defined('BACK_END')) {

            $title = $this->BadgesLanguage->get('general', 'title');


            if ($user->hasPermission('admincp.badges')) {

                $navs[2]->add('badges_divider', mb_strtoupper($title, 'UTF-8'), 'divider', 'top', null, $order);

                $navs[2]->add('badges_items', $title, URL::build('/panel/badges'), 'top', null, $order + 0.1, $icon);
            }
        }
    }

    public function getDebugInfo(): array
    {
        return [];
    }
}
