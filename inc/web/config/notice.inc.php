<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "wxtemplate";
if ($op == "wxtemplate") {
    $_W["page"]["title"] = "微信模板消息";
    if ($_W["ispost"]) {
        $wx_template = $_GPC["wechat"];
        set_system_config("notice.wechat", $wx_template);
        imessage(error(0, "微信模板消息设置成功"), referer(), "ajax");
    }
    $wechat = $_config["notice"]["wechat"];
    include itemplate("config/notice-wechat");
}