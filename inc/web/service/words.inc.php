<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "常用语列表";
    if (checksubmit()) {
        if (!empty($_GPC["ids"])) {
            foreach ($_GPC["ids"] as $k => $v) {
                $data = array("content" => trim($_GPC["content"][$k]));
                pdo_update("hello_banbanjia_service_words", $data, array("uniacid" => $_W["uniacid"], "id" => intval($v)));
            }
        }
        imessage("编辑常用语成功", iurl("service/words/list"), "success");
    }
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_service_words") . $condition, $params);
    $words = pdo_fetchall("select * from" . tablename("hello_banbanjia_service_words") . $condition . " limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    include itemplate("service/words");
}
if ($op == "post") {
    $_W["page"]["title"] = "编辑常用语";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $words = pdo_get("hello_banbanjia_service_words", array("uniacid" => $_W["uniacid"], "id" => $id));
        if (empty($words)) {
            imessage("常用语不存在或已删除", referer(), "error");
        }
    }
    if ($_W["ispost"]) {
        $data = array("uniacid" => $_W["uniacid"], "content" => $_GPC['content'], "status" => intval($_GPC["status"]),'add_time' => time());
        if (!empty($words)) {
            pdo_update("hello_banbanjia_service_words", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        } else {
            pdo_insert("hello_banbanjia_service_words", $data);
        }
        imessage(error(0, "编辑常用语成功"), iurl("service/words/list"), "ajax");
    }
    include itemplate("service/words");
}
if ($op == "del") {
    $ids = $_GPC["id"];
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_delete("hello_banbanjia_service_words", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    imessage(error(0, "删除常用语成功"), referer(), "ajax");
}
if ($op == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_service_words", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}