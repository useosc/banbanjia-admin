<?php
error_reporting(E_ERROR);
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'list';
if ($ta == 'list') {
    $_W['page']['title'] = '银行账号管理';
    if (checksubmit("submit")) { }
    $condition = " uniacid = :uniacid and sid = :sid";
    $params[":uniacid"] = $_W['uniacid'];
    $params[':sid'] = $_W['sid'];
    $keyword = trim($_GPC['keyword']);
    if (!empty($_GPC['keyword'])) {
        $condition .= " and (name like '%" . $keyword . "%' or card = '" . $keyword . "or holders like '%" . $keyword . "%')";
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store_bank_account") . " WHERE " . $condition, $params);
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store_bank_account") . " WHERE " . $condition . " ORDER BY displayorder DESC,id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
}

if ($ta == 'post') {
    $_W['page']['title'] = '添加银行账号';
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $bankAccount = pdo_get("hello_banbanjia_store_bank_account", array("uniacid" => $_W['uniacid'], 'id' => $id));
    }
    if ($_W['ispost']) {
        $insert = array(
            "uniacid" => $_W['uniacid'], 'sid' => $_W['sid'],
            "name" => trim($_GPC['name']), 'card' => trim($_GPC['card']), 'address' => trim($_GPC['card']),
            'holders' => trim($_GPC['holders']), 'displayorder' => intval($_GPC['displayorder']), 'status' => 1,
            'addtime' => TIMESTAMP
        );
        pdo_insert("hello_banbanjia_store_bank_account",$insert);
        imessage("编辑成功", iurl("store/shop/bankAccount"), "success");
    }
}

if ($ta == "del") {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_store_bank_account", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除成功"), iurl("store/shop/bankAccount"), "ajax");
}

if ($ta == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_store_bank_account", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}
include itemplate('store/shop/bankAccount');
