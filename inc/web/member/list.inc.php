<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "顾客列表";
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";

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
include itemplate("member/list");
