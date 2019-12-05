<?php
error_reporting(E_ALL ^ E_NOTICE);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('store');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == 'list') {
    $_W["page"]["title"] = "入驻列表";
    $condition = " where uniacid = :uniacid and addtype = 2";
    $params[":uniacid"] = $_W["uniacid"];
    $status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
    if (0 < $status) {
        $condition .= " AND status = :status";
        $params[":status"] = $status;
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 20;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store") . $condition, $params);
    $lists = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store") . $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    if (!empty($lists)) {
        foreach ($lists as &$li) {
            $li["user"] = store_manager($li["id"]);
        }
    }
    $store_status = store_status();
    $pager = pagination($total, $pindex, $psize);
}
if ($op == "audit") {
    $id = intval($_GPC["id"]);
    $store = pdo_get("hello_banbanjia_store", array("uniacid" => $_W["uniacid"], "id" => $id));
    if (empty($store)) {
        imessage(error(-1, "企业不存在或已删除"), "", "ajax");
    }
    $status = intval($_GPC["status"]);
    if ($status == 1) { //审核通过（生成工作流）
        mload()->lmodel('bpm');
        $store_manager = store_manager($id);
        $site_name = date("dMYHis") . random(3, true);
        $site_data = array('name' => $site_name, 'user' => $store_manager['mobile'], 'pass' => $store_manager['password'], 'pass1' => $store_manager['password']);
        $site_status = createBpmWorkspace($site_data);
        if (is_error($site_status)) {
            imessage("获取生成工作流出错", referer(), "error");
        }
        $site_content = json_decode($site_status['content']);
        $site_message = $site_content->message;
        // var_dump($site_content);exit;

        pdo_update("hello_banbanjia_store", array("bpm_name" => $site_name, "bpm_client_id" => $site_message->CLIENT_ID, "bpm_client_secret" => $site_message->CLIENT_SECRET), array("uniacid" => $_W['uniacid'], "id" => $id));
    }
    pdo_update("hello_banbanjia_store", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
    $remark = trim($_GPC["remark"]);
    // sys_notice_settle($store["id"], "clerk", $remark);
    imessage(error(0, "企业审核成功"), "", "ajax");
}
include itemplate("merchant/settle");
