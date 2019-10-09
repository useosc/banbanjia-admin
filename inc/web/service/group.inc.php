<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "分组列表";
    if (checksubmit()) {
        if (!empty($_GPC["ids"])) {
            foreach ($_GPC["ids"] as $k => $v) {
                $data = array("name" => trim($_GPC["name"][$k]));
                pdo_update("hello_banbanjia_service_groups", $data, array("uniacid" => $_W["uniacid"], "id" => intval($v)));
            }
        }
        imessage("编辑分组成功", iurl("service/group/list"), "success");
    }
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $name = isset($_GPC["name"]) ? trim($_GPC["name"]) : "";
    if (!empty($name)) {
        $condition .= " and name = :name";
        $params[":name"] = $name;
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_service_groups") . $condition, $params);
    $groups = pdo_fetchall("select * from" . tablename("hello_banbanjia_service_groups") . $condition . " limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    include itemplate("service/group");
}
if ($op == "post") {
    $_W["page"]["title"] = "编辑分组";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $groups = pdo_get("hello_banbanjia_service_groups", array("uniacid" => $_W["uniacid"], "id" => $id));
        if (empty($groups)) {
            imessage("分组不存在或已删除", referer(), "error");
        }
    }
    if ($_W["ispost"]) {
        $name = trim($_GPC["name"]) ? trim($_GPC["name"]) : imessage(error(-1, "分组名称不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "name" => $name,"status" => intval($_GPC["status"]));
        if (!empty($groups)) {
            pdo_update("hello_banbanjia_service_groups", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        } else {
            pdo_insert("hello_banbanjia_service_groups", $data);
        }
        imessage(error(0, "编辑分组成功"), iurl("service/group/list"), "ajax");
    }
    include itemplate("service/group");
}
if ($op == "del") {
    $ids = $_GPC["id"];
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_delete("hello_banbanjia_service_groups", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    imessage(error(0, "删除分组成功"), referer(), "ajax");
}
if ($op == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_service_groups", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}