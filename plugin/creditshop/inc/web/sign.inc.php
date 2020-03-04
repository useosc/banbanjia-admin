<?php
defined("IN_IA") or exit("Access Denied");
// error_reporting(E_ALL);
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == 'list') {
    $_W["page"]["title"] = "签到列表";
    $condition = " where s.uniacid = :uniacid and s.uid = m.uid";
    $condition2 = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_sign_log") . $condition2, $params);
    $lists = pdo_fetchall("SELECT s.*,m.avatar as avatar,m.nickname as nickname FROM " . tablename("hello_banbanjia_sign_log") .' as s,' . tablename("hello_banbanjia_members") . ' as m '. $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);

    $pager = pagination($total, $pindex, $psize);
}
if ($op == 'post') {
    $_W["page"]["title"] = "签到规则";
    $sign = get_system_config('member.sign'); 
    if ($_W["ispost"]) {
        $sign = $_GPC['sign'];
        set_system_config("member.sign", $sign);
        imessage(error(0, "编辑签到规则成功"), iurl("creditshop/sign/list"), "ajax");
    }
}
include itemplate("sign");
