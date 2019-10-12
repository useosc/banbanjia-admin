<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
if ($op == "index") {
    $_W["page"]["title"] = "企业账户";
    $stores = pdo_fetchall("select id, title, logo from " . tablename("hello_banbanjia_store") . " where uniacid = :uniacid and status != 4", array(":uniacid" => $_W["uniacid"]), "id");
    if (!empty($stores)) {
        $stores_ids = implode(",", array_keys($stores));
        pdo_query("delete from " . tablename("hello_banbanjia_store_account") . " where uniacid = :uniacid and sid not in (" . $stores_ids . ")", array(":uniacid" => $_W["uniacid"]));
        $condition = " as a left join " . tablename("hello_banbanjia_store_account") . " as b on a.id = b.sid where a.uniacid = :uniacid and a.status != 4";
        $params = array(":uniacid" => $_W["uniacid"]);
        $agentid = intval($_GPC["agentid"]);
        if (0 < $agentid) {
            $condition .= " and a.agentid = :agentid";
            $params[":agentid"] = $agentid;
        }
        $sid = trim($_GPC["sid"]);
        if (!empty($sid)) {
            $condition .= " and b.sid = :sid";
            $params[":sid"] = $sid;
        }
        $pindex = max(1, intval($_GPC["page"]));
        $psize = 15;
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store") . $condition, $params);
        $accounts = pdo_fetchall("SELECT a.*, b.sid, b.amount, b.fee_limit, b.fee_max, b.fee_min, b.fee_rate, b.id as bid, b.deposit, b.fee_period FROM " . tablename("hello_banbanjia_store") . $condition . " ORDER BY b.id ASC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    }
    $pager = pagination($total, $pindex, $psize);
}
// exit;
include itemplate("merchant/account");
