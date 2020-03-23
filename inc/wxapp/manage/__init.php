<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('manage');
mload()->lmodel('clerk');
mload()->lmodel('common');

if ($_W['_ac'] != 'auth') { //如果不是登录页面，就检查是否登录
    icheckmanage();
    $sids = pdo_getall("hello_banbanjia_store_clerk", array('uniacid' => $_W['uniacid'], 'clerk_id' => $_W['manager']['id']), array(), 'sid');
    if (empty($sids)) {
        imessage(error(-1, "您没有管理公司的权限"), "", "ajax");
    }
    if ($_W['_ac'] != 'home') {
        $sid = intval($_GPC["sid"]) ? intval($_GPC["sid"]) : intval($_GPC["__mg_sid"]);
        if (empty($sid)) {
            imessage(error(-1000, "请先选择要管理的公司"), "", "ajax");
        }
        $permits = pdo_get('hello_banbanjia_store_clerk', array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'clerk_id' => $_W['manager']['id']));
        if (empty($permits)) {
            isetcookie('__mg_sid', 0, -1000);
            imessage(error(-1,'您没有该企业的管理权限'),'','ajax');
        }
        $extra = iunserializer($permiss["extra"]);
        $_W['manager']['extra'] = $extra;
        isetcookie("__mg_sid", $sid, 86400 * 7);
        $_GPC["__mg_sid"] = $sid;
        $store = store_fetch($sid);
        $store["account"] = pdo_get("hello_banbanjia_store_account", array("uniacid" => $_W["uniacid"], "sid" => $store["id"]));
        if (!empty($store["account"])) {
            $store["account"]["wechat"] = iunserializer($store["account"]["wechat"]);
            $store["account"]["alipay"] = iunserializer($store["account"]["alipay"]);
            $store["account"]["bank"] = iunserializer($store["account"]["bank"]);
        }
        $_W['we7_hello_banbanjia']['store'] = $store;

        $_W['permits'] = 'all';
        $user = get_clerk($_W['manager']['id']);
        if (empty($user["status"])) {
            imessage("您的账号已禁用，请联系管理员！", "", "error");
        }
        $_W['permits'] = $user['permits'];
        // $permit = (string) $_W['_ac'] . '.' . $_W['_op'];
        // if (!check_permit($permit, true)) {
        //     imessage("您没有权限进行该操作！", "", "error");
        // }


    }
    
}

$_W['sid'] = $sid;
$_W["role"] = "clerker";
$_W["role_cn"] = "企业员工:" . $_W["manager"]["title"];
