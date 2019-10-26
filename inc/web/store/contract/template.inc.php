<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
// header('location:' . iurl('offer/members/list'));
// exit;
if ($ta == 'list') {
    $_W['page']['title'] == '合同模板';
    $condition = " where uniacid = :uniacid and sid = :sid";
    $params = array(":uniacid" => $_W['uniacid'], ":sid" => $sid);
    $status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
    if (-1 < $status) {
        $condition .= " and status = :status";
        $params["status"] = $status;
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_hetong") . $condition, $params);
    $hetongs = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_hetong") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    $cates = pdo_fetchall("select id, catename from " . tablename("hello_banbanjia_store_hetong_category") . " where uniacid = :uniacid and sid = :sid", array(":uniacid" => $_W["uniacid"], ":sid" => $sid), "id");
}
if ($ta == 'post') {
    $_W['page']['title'] == '编辑模板';
    $id = intval($_GPC["id"]);
    if (!empty($id)) {
        $hetong = pdo_get("hello_banbanjia_store_hetong", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    $cates = pdo_fetchall("select id, catename from " . tablename("hello_banbanjia_store_hetong_category") . " where uniacid = :uniacid and sid = :sid", array(":uniacid" => $_W["uniacid"], ":sid" => $sid), "id");
    if ($_W['ispost']) {
        $data = array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'displayorder' => intval($_GPC['displayorder']), 'title' => trim($_GPC['title']), 'data' => htmlspecialchars_decode($_GPC['data']), 'status' => intval($_GPC['status']), 'addtime' => TIMESTAMP, 'cateid' => intval($_GPC['cateid']));
        if (!empty($id)) {
            pdo_update("hello_banbanjia_store_hetong", $data, array('uniacid' => $_W['uniacid'], 'id' => $id));
        } else {
            pdo_insert("hello_banbanjia_store_hetong", $data);
        }
        imessage(error(0, "更新模板成功"), iurl("store/contract/template"), "ajax");
    }
}
if ($ta == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_store_hetong", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}
if ($ta == 'del') {
    $ids = $_GPC['id'];
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_delete("hello_banbanjia_store_hetong", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    imessage(error(0, "删除合同模板成功"), "", "ajax");
}
include itemplate('store/contract/template');
