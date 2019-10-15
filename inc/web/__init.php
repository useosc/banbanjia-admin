<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $GPC;
mload()->lfunc("web"); //创建二级菜单函数
mload()->lfunc("tpl.web");
mload()->lmodel("common"); //权限检查
mload()->lmodel('store'); //商户

$_W["we7_hello_banbanjia"]["global"] = get_global_config(); //全局配置
if ($_W["we7_hello_banbanjia"]["global"]["development"] == 1) {
    ini_set("display_errors", "1");
    error_reporting(30719 ^ 8);
}

$_W["we7_hello_banbanjia"]["config"] = get_system_config();
if (empty($_W["page"]["copyright"]["sitename"])) { //设置sitename
    $_W["page"]["copyright"]["sitename"] = $_W["we7_hello_banbanjia"]["config"]["mall"]["title"];
}

//判断权限
$permissions = array(
    "manager" => array("controller" => array("system")),
    "operator" => array("controller" => array("system", "permit")),
    "merchanter" => array("controller" => array("common", "store", "oauth")),
    "agenter" => array("controller" => array("common", "order", "paycenter", "merchant", "store", "dashboard", "deliveryer", "clerk", "member", "finance", "config", "service", "plugin", "statcenter", "oauth", "plugin", "errander", "bargain", "agent", "diypage", "zhunshibao", "permit")), "agent_operator" => array("controller" => array("common", "order", "paycenter", "merchant", "store", "dashboard", "deliveryer", "clerk", "member", "finance", "config", "service", "plugin", "statcenter", "oauth", "plugin", "errander", "bargain", "agent", "diypage", "zhunshibao", "permit"))
);
if (!empty($_W["isfounder"])) { //创始人
    $_W["role"] = "founder";
}
$permission = true;
if ($_W["role"] == "manager") {
    if (in_array($_W["_ctrl"], $permissions["manager"]["controller"])) {
        $permission = false;
    }
} else {
    if ($_W["role"] == "operator") {
        if (in_array($_W["_ctrl"], $permissions["operator"]["controller"])) {
            $permission = false;
        }
    } else {
        if ($_W["role"] == "merchanter") {
            if (!in_array($_W["_ctrl"], $permissions["merchanter"]["controller"])) {
                $permission = false;
            }
        }
    }
}
if (!$permission) {
    if ($_W["ispost"]) {
        imessage(error(-1, "您没有权限进行该操作"), "", "ajax");
    }
    imessage("您没有权限进行该操作", "", "info");
}
$_W['permits'] = 'all';
if ($_W['role'] == 'operator') { //平台后台权限
    $user = get_user();
    if (empty($user["status"])) {
        imessage("您的账号已禁用，请联系管理员！", "", "error");
    }
    $_W['permits'] = $user['permits'];
    $permit = (string) $_W['_ctrl'] . '.' . $_W['_ac'];
    if (!check_permit($permit, true)) {
        $_W["_plugin"]["name"] = $_W["_ctrl"];
        imessage("您没有权限进行该操作！", "", "error");
    }
}
if ($_W['role'] == 'merchanter') { //商户后台权限
    $user = get_clerk($_W['clerk']['id']);
    if (empty($user["status"])) {
        imessage("您的账号已禁用，请联系管理员！", "", "error");
    }
    $_W['permits'] = $user['permits'];
    $permit = (string) $_W['_ac'] . '.' . $_W['_op'];
    if (!check_permit($permit, true)) {
        $_W["_plugin"]["name"] = $_W["_ctrl"];
        imessage("您没有权限进行该操作！", "", "error");
    }
}
//企业入口
if (defined('IN_MERCHANT')) {
    if (!in_array($_W['role'], array('manager', 'operator', 'founder', 'merchanter')) && empty($_W['we7_hello_banbanjia']['store']) && (empty($_W['_ac']) || $_W['_op'] != 'login')) {
        imessage('抱歉，您无权进行该操作，请先登录！', iurl('store/oauth/login'), 'info');
    }
    if ($_W['_op'] != 'login') {
        if ($_W["role"] == "merchanter") {
            $sid = intval($_W["we7_hello_banbanjia"]["store"]["id"]);
        } else {
            if (!empty($_GPC["_sid"])) {
                $sid = intval($_GPC["_sid"]);
                isetcookie("__sid", $sid, 86400);
            } else {
                $sid = intval($_GPC["__sid"]);
            }
            if ($_GPC["add_store"] == 1) {
                $sid = 0;
            }
        }
        $_W["we7_hello_banbanjia"]["sid"] = $sid;
        isetcookie("__sid", $sid, 86400);
        $store = pdo_get("hello_banbanjia_store", array("uniacid" => $_W["uniacid"], "id" => $sid));
        $store["data"] = iunserializer($store["data"]);
        $_W["store"] = $store;
        if (!$_GPC["add_store"] && empty($store)) {
            imessage("企业不存在或已删除！", referer(), "error");
        }
    }
}
$_W["isoperator"] = $_W["role"] == "operator";
$_W["ismanager"] = $_W["role"] == "manager" || !empty($_W["isfounder"]);
$_W['role_cn'] == '平台创始人';
if ($_W["role"] == "manager") {
    $_W["role_cn"] = "公众号管理员:" . $_W["user"]["username"];
} else {
    if ($_W["role"] == "operator") {
        $_W["role_cn"] = "公众号操作员:" . $_W["user"]["username"];
    } else {
        if ($_W["role"] == "merchanter") {
            $_W["role_cn"] = "公司管理员:" . $_W["user"]["username"];
        } else {
            if ($_W["role"] == "agenter") {
                $_W["role_cn"] = "代理商:" . $_W["agent"]["realname"];
            }
        }
    }
}
