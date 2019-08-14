<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "房间类型";
    if ($_W["ispost"]) {
        if (!empty($_GPC["ids"])) {
            foreach ($_GPC["ids"] as $k => $v) {
                $data = array("title" => trim($_GPC["title"][$k]), "thumb" => trim($_GPC['thumb']), "displayorder" => intval($_GPC["displayorder"][$k]));
                pdo_update("hello_banbanjia_room_category", $data, array("uniacid" => $_W["uniacid"], "id" => intval($v)));
            }
        }
        imessage(error(0, "编辑成功"), referer(), "ajax");
    }
    $condition = " WHERE uniacid = :uniacid";
    $params[":uniacid"] = $_W["uniacid"];
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_room_category") . $condition, $params);
    $lists = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_room_category") . $condition . " ORDER BY id LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
}
if ($op == "post") {
    $_W["page"]["title"] = "添加房间";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_room_category", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    if ($_W["ispost"]) {
        $_GPC["title"] = trim($_GPC["title"]) ? trim($_GPC["title"]) : imessage(error(-1, "房间名称不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "thumb" => trim($_GPC['thumb']), "title" => $_GPC["title"], "displayorder" => intval($_GPC["displayorder"]));
        if (!$id) {
            pdo_insert("hello_banbanjia_room_category", $data);
        } else {
            pdo_update("hello_banbanjia_room_category", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑房间成功"), iurl("config/roomCategory/list"), "ajax");
    }
}
if ($op == "del") {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_room_category", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除分类成功"), "", "ajax");
}
include itemplate("config/roomCategory");
