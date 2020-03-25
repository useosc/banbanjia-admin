<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list') {
    $_W["page"]["title"] = "房间列表";
    $condition = " WHERE uniacid = :uniacid and sid = :sid";
    $params[":uniacid"] = $_W["uniacid"];
    $params[":sid"] = $sid;
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store_room_category") . $condition, $params);
    $lists = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store_room_category") . $condition . " ORDER BY id LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    // $pager = pagination($total, $pindex, $psize);
    $result = array('total' => $total,'list' => $lists);
    imessage(error(0,$result),'','ajax');
}

if($ta == 'post') {
    $_W["page"]["title"] = "添加房间";
    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {
        $_GPC["title"] = trim($_GPC["title"]) ? trim($_GPC["title"]) : imessage(error(-1, "房间名称不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "sid" => $sid, "thumb" => trim($_GPC['thumb']), "title" => $_GPC["title"], "displayorder" => intval($_GPC["displayorder"]));
        if (!$id) {
            pdo_insert("hello_banbanjia_store_room_category", $data);
        } else {
            pdo_update("hello_banbanjia_store_room_category", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑房间成功"), "", "ajax");
    }
    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_store_room_category", array("uniacid" => $_W["uniacid"], "id" => $id));
        imessage(error(0, $item), "", "ajax");
    }
}

if($ta == 'del') {
    $id = intval($_GPC["id"]);
    // 查找是否有商品用了此分类
    $goods = pdo_getall("hello_banbanjia_store_goods",array("uniacid" => $_W['uniacid'],"cateid" => $id));
    if(!empty($goods)) {
        imessage(error(-1, "删除失败，不能删除已被使用的分类！"), "", "ajax");
    }
    pdo_delete("hello_banbanjia_store_room_category", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除分类成功"), "", "ajax");
}
