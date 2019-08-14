<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "物品列表";
    $categorys = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_room_category") . " WHERE uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]));
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $condition = " WHERE a.uniacid = :uniacid";
    $params[":uniacid"] = $_W["uniacid"];
    if (!empty($_GPC["cateid"])) {
        $condition .= " and cateid = :cateid";
        $params[":cateid"] = intval($_GPC["cateid"]);
    }
    $createtime = intval($_GPC["createtime"]);
    if (!empty($createtime)) {
        $time = TIMESTAMP - $createtime * 24 * 60 * 60;
        $condition .= " and addtime > :time";
        $params[":time"] = $time;
    }
    $good = $_GPC["title"];
    if (!empty($good)) {
        $condition .= " and a.title like '%" . $good . "%'";
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_goods") . "as a left join" . tablename("hello_banbanjia_room_category") . " as b on a.cateid = b.id" . $condition, $params);
    $lists = pdo_fetchall("SELECT *,a.thumb as athumb,a.id as aid,a.title as atitle,a.volume as avolume,b.title as btitle,a.displayorder as adisplayorder FROM " . tablename("hello_banbanjia_goods") . " as a left join" . tablename("hello_banbanjia_room_category") . " as b on a.cateid = b.id" . $condition . " ORDER BY a.displayorder LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    if ($_W["ispost"]) {
        if (!empty($_GPC["ids"])) {
            foreach ($_GPC["ids"] as $k => $v) {
                $data = array("title" => trim($_GPC["title"][$k]), "displayorder" => intval($_GPC["displayorder"][$k]), "volume" => floatval($_GPC["volume"][$k]));
                pdo_update("hello_banbanjia_goods", $data, array("uniacid" => $_W["uniacid"], "id" => intval($v)));
            }
        }
        imessage(error(0, "编辑成功"), referer(), "ajax");
    }
}
if ($op == "post") {
    $_W["page"]["title"] = "添加物品";
    $categorys = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_room_category") . " WHERE uniacid = :uniacid ORDER BY id DESC", array(":uniacid" => $_W["uniacid"]));
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_goods", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    if ($_W["ispost"]) {
        $_GPC["title"] = trim($_GPC["title"]) ? trim($_GPC["title"]) : imessage(error(-1, "物品名称不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "title" => $_GPC["title"], "cateid" => intval($_GPC["cateid"]), "volume" => floatval($_GPC["volume"]), "thumb" => trim($_GPC["thumb"]), "is_display" => intval($_GPC["is_display"]), "displayorder" => intval($_GPC["displayorder"]), "addtime" => TIMESTAMP);
        if (!$id) {
            pdo_insert("hello_banbanjia_goods", $data);
        } else {
            pdo_update("hello_banbanjia_goods", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑物品成功"), iurl("config/goods/list"), "ajax");
    }
}
if ($op == "del") {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_goods", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除物品成功"), "", "ajax");
}
if ($op == "is_display") {
    $id = intval($_GPC["id"]);
    $is_display = intval($_GPC["is_display"]);
    pdo_update("hello_banbanjia_goods", array("is_display" => $is_display), array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}
include itemplate("config/goods");
