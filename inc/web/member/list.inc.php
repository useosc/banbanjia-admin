<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL);

$_W["page"]["title"] = "顾客列表";
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";

if ($op == 'del') {
    $id = intval($_GPC['id']);
    $uid = intval($_GPC['uid']);
    if (empty($id) || empty($uid)) {
        imessage(error(0, '顾客不存在'), referer(), 'ajax');
    }
    pdo_delete('hello_banbanjia_members', array('uniacid' => $_W['uniacid'], 'id' => $id));
    mlog(6001, $uid);
    imessage(error(0, '删除成功'), referer(), 'ajax');
}
if ($op == "list") {
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $key = trim($_GPC['key']);
    if (!empty($key)) {
        $time = strtotime("-30 days");
        if ($key == "success_30") {
            $condition .= " and success_last_time >= :time";
        }
        $params[":time"] = $time;
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_members") . $condition, $params);
    $data = pdo_fetchall("select * from " . tablename("hello_banbanjia_members") . $condition . " LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
}
if ($op == 'info') {
    $uid = intval($_GPC['uid']);
    $member = pdo_get('hello_banbanjia_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
    if (empty($member)) {
        imessage(error(-1, '顾客不存在或已被删除'), referer(), 'error');
    }
}
include itemplate("member/list");
