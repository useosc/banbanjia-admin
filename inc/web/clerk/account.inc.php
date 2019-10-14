<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "企业员工账户";
    $condition = " WHERE uniacid = :uniacid";
    $params[":uniacid"] = $_W["uniacid"];
    $agentid = intval($_GPC["agentid"]);
    if (0 < $agentid) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and (title like '%" . $keyword . "%' or nickname like '%" . $keyword . "%' or mobile like '%" . $keyword . "%')";
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_clerk") . $condition, $params);
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_clerk") . $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    if (!empty($data)) {
        $stores = pdo_getall("hello_banbanjia_store", array("uniacid" => $_W["uniacid"]), array("id", "title"), "id");
        foreach ($data as &$val) {
            $sids = pdo_getall("hello_banbanjia_store_clerk", array("uniacid" => $_W["uniacid"], "clerk_id" => $val["id"]), array("sid"));
            $val["stores_title"] = "(暂无)";
            if (!empty($sids)) {
                foreach ($sids as $sid) {
                    $stores_title[] = $stores[$sid["sid"]]["title"];
                }
                if (!empty($val["stores_title"])) {
                    $val["stores_title"] = implode("，", $stores_title);
                }
                unset($stores_title);
            }
        }
    }
    $pager = pagination($total, $pindex, $psize);
}
include itemplate("clerk/account");