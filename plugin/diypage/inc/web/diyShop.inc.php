<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
if ($op == 'index') {
    $_W["page"]["title"] = "页面选择";
    mload()->lmodel('diy');
    $pages = array(
        "home" => array(
            'name' => '平台首页',
            'url' => 'pages/home/index',
            'key' => 'home',
            'save_key' => 'use_diy_home',
            'pages' => get_wxapp_pages(array('type' => 1, 'from' => 'wap'), array('id', 'name'))
        ),
        'member' => array(
            'name' => '会员中心',
            'url' => 'pages/my/my',
            'key' => 'member',
            'save_key' => 'use_diy_member',
            'pages' => get_wxapp_pages(array('type' => 2, 'from' => 'wap'), array('id', 'name'))
        )
    );
    if(check_plugin_exist('gohome')){
        $pages['article'] = array(
            'name' => '社区文章',
            'url' => "gohome/pages/article/index",
            'key' => 'article',
            'save_key' => 'use_diy_article',
            "pages" => get_wxapp_pages(array("type" => 4, "from" => "wap"), array("id", "name"))
        );
    }
    if($_W['ispost']){
        $setting_vue = array("use_diy_home" => intval($_GPC["vue_use_diy_home"]), "use_diy_member" => intval($_GPC["vue_use_diy_member"]), "use_diy_gohome" => intval($_GPC["vue_use_diy_gohome"]), "use_diy_article" => intval($_GPC["vue_use_diy_article"]), "use_diy_haodian" => intval($_GPC["vue_use_diy_haodian"]), "shopPage" => array_map("intval", $_GPC["vue_shopPages"]));
        set_plugin_config("diypage.diy", $setting_vue);
        // $setting_wxapp = array("use_diy_home" => intval($_GPC["wxapp_use_diy_home"]), "use_diy_member" => intval($_GPC["wxapp_use_diy_member"]), "use_diy_gohome" => intval($_GPC["wxapp_use_diy_gohome"]), "use_diy_article" => intval($_GPC["wxapp_use_diy_article"]), "use_diy_haodian" => intval($_GPC["wxapp_use_diy_haodian"]), "shopPage" => array_map("intval", $_GPC["wxapp_shopPages"]));
        // set_plugin_config("wxapp.diy", $setting_wxapp);
        set_plugin_config("wxapp.diy", $setting_vue);
        imessage(error(0, "编辑成功"), referer(), "ajax");
    }
    $config_diy_vue = get_plugin_config("diypage.diy");
    $config_diy_wxapp = get_plugin_config("wxapp.diy");
}
include itemplate('diyShop');
