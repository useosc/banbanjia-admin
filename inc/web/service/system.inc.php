<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]);
if ($op == 'reply') {
    $_W['page']['title'] = '自动回复设置';
    $reply = pdo_fetch("SELECT * FROM " . tablename("hello_banbanjia_service_reply") . " WHERE uniacid = :uniacid and id = 1", array(":uniacid" => $_W['uniacid']));

    if ($_W['ispost']) {
        $data = array("word" => $_GPC['word'], "uniacid" => $_W['uniacid'], "status" => intval($_GPC['status']));
        pdo_update("hello_banbanjia_service_reply", $data, array("id" => 1));
        imessage(error(0, "自动回复设置成功"), referer(), "ajax");
    }
} else {
    if ($op == 'customer') {
        $_W['page']['title'] = '客服设置';
        $config = pdo_fetch("SELECT * FROM " . tablename("hello_banbanjia_service_config") . " WHERE id = 1");

        if ($_W['ispost']) {
            $data = array("max_service" => $_GPC['max_service'], "change_status" => intval($_GPC['change_status']));
            pdo_update("hello_banbanjia_service_config", $data, array("id" => 1));
            imessage(error(0, "客服设置成功"), referer(), "ajax");
        }
    }
}
include itemplate('service/system');
