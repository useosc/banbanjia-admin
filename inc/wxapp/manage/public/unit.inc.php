<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list') {
    $_W["page"]["title"] = "单位列表";

    $units = pdo_getall("hello_banbanjia_store_unit",array("uniacid" => $_W['uniacid'],"sid" => $_W['sid']));

    imessage(error(0,$units),'','ajax');
}

if($ta == 'post') {
    $_W["page"]["title"] = "添加单位";
    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {
        $_GPC["name"] = trim($_GPC["name"]) ? trim($_GPC["name"]) : imessage(error(-1, "单位名称不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "sid" => $sid,  "name" => $_GPC["name"]);
        if (!$id) {
            pdo_insert("ims_hello_banbanjia_store_unit", $data);
            imessage(error(0, "新建单位成功"), "", "ajax");
        } else {
            pdo_update("ims_hello_banbanjia_store_unit", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑单位成功"), "", "ajax");
    }
    if (0 < $id) {
        $item = pdo_get("ims_hello_banbanjia_store_unit", array("uniacid" => $_W["uniacid"], "id" => $id));
        imessage(error(0, $item), "", "ajax");
    }
}

if($ta == 'del') {
    $id = intval($_GPC["id"]);
    // 查找是否有商品用了此单位
    $goods = pdo_getall("hello_banbanjia_store_goods",array("uniacid" => $_W['uniacid'],"unitid" => $id));
    if(!empty($goods)) {
        imessage(error(-1, "删除失败，不能删除已被使用的单位！"), "", "ajax");
    }
    pdo_delete("ims_hello_banbanjia_store_unit", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除单位成功"), "", "ajax");
}
