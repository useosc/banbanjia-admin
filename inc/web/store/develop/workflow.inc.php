<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;

$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";

if ($ta == 'list') {
    $_W["page"]["title"] = "流程列表";
    $condition = " where uniacid = :uniacid and sid = :sid";
    $params = array(":uniacid" => $_W['uniacid'], ":sid" => $sid);

    $pindex = max(1, intval($_GPC['page']));
    $psize = 30;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_workflow") . $condition, $params);
    $data = pdo_fetchall("SELECT * from " . tablename("hello_banbanjia_store_workflow") . $condition . " LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);

    include itemplate('store/develop/workflowList');
}
if ($ta == 'post') {
    $_W["page"]["title"] = "流程编辑";
    $id = intval($_GPC['id']);
    if (0 < $id) {
        $workflow = pdo_get("hello_banbanjia_store_workflow", array('uniacid' => $_W['uniacid'], 'id' => $id));
    }
    if ($_W['ispost']) {
        $data = array('uniacid' => $_W['uniacid'], 'sid' => $sid, 'wf_uid' => uuid(), 'name' => trim($_GPC['name']), 'filename' => $_GPC['filename'], 'createtime' => TIMESTAMP, 'version' => '1.0');
        if (!empty($workflow["id"])) {
            pdo_update("hello_banbanjia_store_workflow", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        } else {
            pdo_insert("hello_banbanjia_store_workflow", $data);
        }
        imessage(error(0, "编辑流程成功"), iurl("store/develop/workflow/list"), "ajax");
    }

    include itemplate('store/develop/workflowPost');
}

if ($ta == 'flow') {
    $_W["page"]["title"] = "流程图";
    $id = intval($_GPC['id']);
    if (0 < $id) {
        $workflow = pdo_get("hello_banbanjia_store_workflow", array('uniacid' => $_W['uniacid'], 'id' => $id, 'sid' => $sid));
    }else{
        imessage(error(-1,'请选择有效的流程'),'','refer');
    }

    include itemplate('store/develop/workflowFlow');
}
