<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "basic";
if($op == 'basic'){
    $_W['page']['title'] = '基础设置';
    if($_W['ispost']){
        $mall = array('title' => trim($_GPC['title']),'logo'=>trim($_GPC['logo']),'mobile'=>trim($_GPC['mobile']),'version' => intval($_GPC['version']),'is_to_nearest_store' => intval($_GPC['is_to_nearest_store']),'store_orderby_type' => trim($_GPC['store_orderby_type']),'store_overradius_display'=>intval($_GPC['store_overradius_display']),'copyright'=>htmlspecialchars_decode($_GPC['copyright']));
        set_system_config('mall',$mall);
        $manager = $_GPC["manager"];
        set_system_config("manager", $manager);
        imessage(error(0, "基础设置成功"), referer(), "ajax");
    }
    $config = $_config["mall"];
    $config["manager"] = $_config["manager"];

    include itemplate('config/basic');
}