<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "wiki";
if ($op == 'chatlog') {
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

    include itemplate('service/robot/chatlog');
}

if ($op == 'wiki') {
    // error_reporting(E_ALL);
    $_W["page"]["title"] = "知识库";
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $agentid = intval($_GPC["agentid"]);
    if (0 < $agentid) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_service_robot_wiki") . $condition, $params);
    $notices = pdo_fetchall("select * from" . tablename("hello_banbanjia_service_robot_wiki") . $condition . " limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);

    include itemplate('service/robot/wiki');
}
if ($op == "post") {
    // error_reporting(E_ALL);
    $_W["page"]["title"] = "编辑问答";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $notice = pdo_get("hello_banbanjia_service_robot_wiki", array("uniacid" => $_W["uniacid"], "id" => $id));
    }
    if (empty($notice)) {
        $notice = array("status" => 1);
    }
    if ($_W["ispost"]) {
        $data = array("uniacid" => $_W["uniacid"], "title" => trim($_GPC["title"]), "content" => htmlspecialchars_decode($_GPC["content"]),"status" => intval($_GPC["status"]), "addtime" => TIMESTAMP);
        if (!empty($notice["id"])) {
            pdo_update("hello_banbanjia_service_robot_wiki", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        } else {
            pdo_insert("hello_banbanjia_service_robot_wiki", $data);
        }
        imessage(error(0, "更新问答成功"), iurl("service/robot/wiki"), "ajax");
    }
    include itemplate('service/robot/wiki');
}
if ($op == "del") {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_service_robot_wiki", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除问答成功"), iurl("service/robot/wiki"), "ajax");
}
if ($op == "status") {
    $id = intval($_GPC["id"]);
    $data = array("status" => intval($_GPC["status"]));
    pdo_update("hello_banbanjia_service_robot_wiki", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}

if ($op == "feedback") {
    $_W["page"]["title"] = "客户反馈";
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $agentid = intval($_GPC["agentid"]);
    if (0 < $agentid) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_service_feedback") . $condition, $params);
    $notices = pdo_fetchall("select * from" . tablename("hello_banbanjia_service_feedback") . $condition . " limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);

    include itemplate('service/robot/feedback');
}

if ($op == "delfb") {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_service_feedback", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除成功"), iurl("service/robot/feedback"), "ajax");
}