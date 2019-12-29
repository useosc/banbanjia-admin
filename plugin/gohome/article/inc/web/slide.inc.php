<?php
defined("IN_IA") or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "post") {
    $_W["page"]["title"] = "编辑幻灯片";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $slide = pdo_get("hello_banbanjia_gohome_slide", array("uniacid" => $_W["uniacid"], "id" => $id));
        if (empty($slide)) {
            imessage("幻灯片不存在或已删除", referer(), "error");
        }
    }
    if ($_W["ispost"]) {
        $title = trim($_GPC["title"]) ? trim($_GPC["title"]) : imessage(error(-1, "标题不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "title" => $title, "thumb" => trim($_GPC["thumb"]), "displayorder" => intval($_GPC["displayorder"]), "type" => "article", "status" => intval($_GPC["status"]), "wxapp_link" => trim($_GPC["wxapp_link"]));
        if (!empty($slide)) {
            pdo_update("hello_banbanjia_gohome_slide", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        } else {
            pdo_insert("hello_banbanjia_gohome_slide", $data);
        }
        imessage(error(0, "编辑幻灯片成功"), iurl("article/slide/list"), "ajax");
    }
    include itemplate("slide");
} 