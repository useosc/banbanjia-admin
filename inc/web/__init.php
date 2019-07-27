<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $GPC;
mload()->lfunc("web"); //创建二级菜单函数
mload()->lfunc("tpl.web");
mload()->lmodel("common"); //权限检查

$_W["we7_hello_banbanjia"]["global"] = get_global_config(); //全局配置
if ($_W["we7_hello_banbanjia"]["global"]["development"] == 1) {
    ini_set("display_errors", "1");
    error_reporting(30719 ^ 8);
}

$_W["we7_hello_banbanjia"]["config"] = get_system_config();
if (empty($_W["page"]["copyright"]["sitename"])) { //设置sitename
    $_W["page"]["copyright"]["sitename"] = $_W["we7_hello_banbanjia"]["config"]["platform"]["title"];
}

//判断权限
$permitissions = array("manager" => array("controller" => array("system")), "operator" => array("controller" => array("system", "permit")), "merchanter" => array("controller" => array("common", "store", "oauth")), "agenter" => array("controller" => array("common", "order", "paycenter", "merchant", "store", "dashboard", "deliveryer", "clerk", "member", "finance", "config", "service", "plugin", "statcenter", "oauth", "plugin", "errander", "bargain", "agent", "diypage", "zhunshibao", "permit")), "agent_operator" => array("controller" => array("common", "order", "paycenter", "merchant", "store", "dashboard", "deliveryer", "clerk", "member", "finance", "config", "service", "plugin", "statcenter", "oauth", "plugin", "errander", "bargain", "agent", "diypage", "zhunshibao", "permit")));
if (!empty($_W["isfounder"])) { //创始人
    $_W["role"] = "founder";
}
$permitission = true;
if ($_W["role"] == "manager") {
    if (in_array($_W["_ctrl"], $permitissions["manager"]["controller"])) {
        $permitission = false;
    }
} else {
    if ($_W["role"] == "operator") {
        if (in_array($_W["_ctrl"], $permitissions["operator"]["controller"])) {
            $permitission = false;
        }
    } else {
        if ($_W["role"] == "merchanter") {
            if (!in_array($_W["_ctrl"], $permitissions["merchanter"]["controller"])) {
                $permitission = false;
            }
        } else {
            if ($_W["role"] == "agenter") {
                if (!in_array($_W["_ctrl"], $permitissions["agenter"]["controller"]) && !defined("IN_GOHOME")) {
                    $permitission = false;
                }
            } else {
                if ($_W["role"] == "agent_operator" && !in_array($_W["_ctrl"], $permitissions["agent_operator"]["controller"]) && !defined("IN_GOHOME")) {
                    $permitission = false;
                }
            }
        }
    }
}
if(!$permitission){
    if($_W["ispost"]){
        imessage(error(-1,"您没有权限进行改操作"),"","ajax");
    }
    imessage("您没有权限进行该操作","","info");
}
