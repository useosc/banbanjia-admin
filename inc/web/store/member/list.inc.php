<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel("member");
$_W["page"]["title"] = "客户列表";
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if ($ta == "list") {
    $condition = " where uniacid = :uniacid and sid = :sid";
    $params = array(":uniacid" => $_W["uniacid"], ":sid" => $sid);
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and uid in (select uid from " . tablename("hello_banbanjia_members") . " where (realname like :keyword or mobile like :keyword))";
        $params[":keyword"] = "%" . $keyword . "%";
    }
    $sort = trim($_GPC["sort"]);
    $sort_val = intval($_GPC["sort_val"]);
    if (!empty($sort)) {
        if ($sort_val == 1) {
            $condition .= " ORDER BY " . $sort . " DESC";
        } else {
            $condition .= " ORDER BY " . $sort . " ASC";
        }
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 40;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_members") . $condition, $params);
    $data = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_members") . $condition . " LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    if (!empty($data)) {
        $users = array();
        foreach ($data as $da) {
            $users[] = $da["uid"];
        }
        $users = implode(",", $users);
        $users = pdo_fetchall("select * from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and uid in (" . $users . ")", array(":uniacid" => $_W["uniacid"]), "uid");
    }
    $pager = pagination($total, $pindex, $psize);
    $stat = member_amount_stat($sid);
}

include itemplate("store/member/list");