<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
icheckauth(true);
if ($op == 'index') {
    if ($_W["ochannel"] == "wxapp" && $_W["is_agent"] && $_W["agentid"] == -1) {
        imessage(error(-2, "您所在的区域暂未获取到社区信息,建议您手动搜索地址或切换到此前常用的地址再试试"), "", "ajax");
    }
    article_cron();
    article_flow_update("falselooknum");
    mload()->lmodel("diy");
    if ($_config_wxapp["diy"]["use_diy_article"] != 1) {
        $pageOrid = get_wxapp_defaultpage("article");
        $config_share = $_config_plugin["share"];
        $share = array("title" => $config_share["title"], "desc" => $config_share["detail"], "link" => empty($config_share["link"]) ? ivurl("gohome/pages/article/index", array(), true) : $config_share["link"], "imgUrl" => tomedia($config_share["thumb"]));
    } else {
        $pageOrid = $_config_wxapp["diy"]["shopPage"]["article"];
        if (empty($pageOrid)) {
            imessage(error(-1, "未设置文章DIY页面"), "", "ajax");
        }
    }
    $page = get_wxapp_diy($pageOrid, true);
    if (empty($page)) {
        imessage(error(-1, "页面不能为空"), "", "ajax");
    }
    // $_W["_share"] = array("title" => $page["data"]["page"]["title"], "desc" => $page["data"]["page"]["desc"], "link" => ivurl("gohome/pages/article/index", array(), true), "imgUrl" => tomedia($page["data"]["page"]["thumb"]));
    if ($_config_wxapp["diy"]["use_diy_article"] != 1) {
        $_W["_share"] = $share;
    }
    $default_location = array();

    $result = array("diy" => $page);
    $_W['_nav'] = 1;
    imessage(error(0, $result), '', 'ajax');
} else {
    if ($op == 'information') { }
}
