<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == 'list') {
    $_W["page"]["title"] = "历史会话";
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;

    if (!empty($_GPC['name'])) {
        $condition .= " from_name like %:name% or to_name like %:name%";
        $params = array(":name" => $_GPC['name']);
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_service_chat_log") . $condition, $params);
    $pager = pagination($total, $pindex, $psize);
    $logs = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_service_chat_log") . $condition . "ORDER BY id DESC " . " LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
}
include itemplate('service/chatlog');
