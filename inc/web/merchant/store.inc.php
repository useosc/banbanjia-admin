<?php
// error_reporting(E_ALL);
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if($op == 'list'){
    $_W['page']['title'] = '企业列表';
    if (checksubmit("submit")) {
        if (!empty($_GPC["ids"])) {
            foreach ($_GPC["ids"] as $k => $v) {
                $data = array("displayorder" => intval($_GPC["displayorder"][$k]), "click" => intval($_GPC["click"][$k]), "sailed" => intval($_GPC["sailed"][$k]));
                pdo_update("hello_banbanjia_store", $data, array("uniacid" => $_W["uniacid"], "id" => intval($v)));
            }
        }
        imessage("编辑成功", iurl("merchant/store/list"), "success");
    }
    $store_label = category_store_label();
    $condition = " uniacid = :uniacid and (status = 1 or status = 0)";
    $params[":uniacid"] = $_W["uniacid"];
    $cid = intval($_GPC["cid"]);
    if (0 < $cid) {
        $condition .= " AND cid LIKE :cid";
        $params[":cid"] = "%|" . $cid . "|%";
    }
    $label = intval($_GPC["label"]);
    if (0 < $label) {
        $condition .= " AND label = :label";
        $params[":label"] = $label;
    }
    $is_rest = isset($_GPC["is_rest"]) ? intval($_GPC["is_rest"]) : -1;
    if (-1 < $is_rest) {
        $condition .= " AND is_rest = :is_rest";
        $params[":is_rest"] = $is_rest;
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($_GPC["keyword"])) {
        $condition .= " and (title like '%" . $keyword . "%' or id = '" . $keyword . "')";
    }

    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store") . " WHERE " . $condition, $params);
    $lists = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store") . " WHERE " . $condition . " ORDER BY displayorder DESC,id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    if (!empty($lists)) {
        foreach ($lists as &$li) {
            $li["cid"] = explode("|", $li["cid"]);
            // $li["wechat_qrcode"] = (array) iunserializer($li["wechat_qrcode"]);
            // $li["wechat_url"] = $li["wechat_qrcode"]["url"];
        }
    }
    $categorys = store_fetchall_category();
    $store_status = store_status();
    include itemplate('merchant/list');
}
if($op == 'post'){
    $_W['page']['title'] = '添加企业';
    
    include itemplate('merchant/post');
}

if($op == 'is_in_business') {
    $sid = intval($_GPC['id']);
    $is_in_business = intval($_GPC['is_in_business']);
    pdo_update("hello_banbanjia_store", array("is_in_business" => $is_in_business), array("uniacid" => $_W["uniacid"], "id" => $sid));
    mlog(2012, $sid);
    imessage(error(0,''),'','ajax');
}
if ($op == "status") {
    $sid = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_store", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $sid));
    imessage(error(0, ""), "", "ajax");
}
if ($op == "is_recommend") {
    $sid = intval($_GPC["id"]);
    $recommend = intval($_GPC["is_recommend"]);
    pdo_update("hello_banbanjia_store", array("is_recommend" => $recommend), array("uniacid" => $_W["uniacid"], "id" => $sid));
    imessage(error(0, ""), "", "ajax");
}
if ($op == "is_stick") {
    $sid = intval($_GPC["id"]);
    $is_stick = intval($_GPC["is_stick"]);
    pdo_update("hello_banbanjia_store", array("is_stick" => $is_stick), array("uniacid" => $_W["uniacid"], "id" => $sid));
    imessage(error(0, ""), "", "ajax");
}