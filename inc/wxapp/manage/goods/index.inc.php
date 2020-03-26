<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
mload()->lmodel("goods");
if($ta == 'list') {
    $records = store_order_fetchall();
    $result = array("goods"=>$records['goods']);
    imessage(error(0,$result),'','ajax');
}

if($ta == 'cglist') {
    error_reporting(E_ERROR);
    // $result = pdo_fetchall(" select * from " . tablename("hello_banbanjia_store_goods") . " where uniacid = :uniacid and sid = :sid group by cateid order by cateid",
    //  array(":uniacid" => $_W['uniacid'],":sid" => $_W['sid']),'id');
    $result = array();
    $cates = pdo_getall("hello_banbanjia_store_room_category",array("uniacid"=>$_W['uniacid'],'sid'=>$sid));
    foreach($cates as $cate) {
        $result[$cate[id]] = pdo_getall("hello_banbanjia_store_goods",array("uniacid"=>$_W['uniacid'],'sid'=>$sid,'cateid'=>$cate['id']));
    }
    //  if(!empty($result)) {

    //  }

     imessage(error(0,$result),'','ajax');
}

if($ta == 'post') {
    // error_reporting(E_ALL);
    $_W["page"]["title"] = "添加家具";
    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {
        $_GPC["title"] = trim($_GPC["title"]) ? trim($_GPC["title"]) : imessage(error(-1, "家具名称不能为空"), "", "ajax");
        $_GPC["cateid"] = intval($_GPC["cateid"]) ? intval($_GPC["cateid"]) : imessage(error(-1, "家具分类不能为空"), "", "ajax");
        $_GPC["carryprice"] = floatval($_GPC["carryprice"]) ? floatval($_GPC["carryprice"]) : imessage(error(-1, "搬运费不能为空"), "", "ajax");
        $_GPC["unloadprice"] = floatval($_GPC["unloadprice"]) ? floatval($_GPC["unloadprice"]) : imessage(error(-1, "拆卸费不能为空"), "", "ajax");
        $_GPC["packprice"] = floatval($_GPC["carryprice"]) ? floatval($_GPC["carryprice"]) : imessage(error(-1, "搬运费不能为空"), "", "ajax");

        $data = array("uniacid" => $_W["uniacid"], "sid" => $sid, "cateid" => $_GPC['cateid'],"catename" => trim($_GPC['catename']), "title" => $_GPC["title"],
        "carryprice" => $_GPC['carryprice'],"unloadprice" => $_GPC['unloadprice'],"packprice" => $_GPC['packprice'],"addtime" => TIMESTAMP,
        "thumbs" => trim($_GPC['thumbs']), "remark" => $_GPC['remark'], "status" => intval($_GPC['status']),"volume" => trim($_GPC['volume']), "displayorder" => intval($_GPC["displayorder"]));
        if (!$id) {
            pdo_insert("hello_banbanjia_store_goods", $data);
        } else {
            pdo_update("hello_banbanjia_store_goods", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑家具成功"), "", "ajax");
    }
    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_store_goods", array("uniacid" => $_W["uniacid"], "id" => $id));
        imessage(error(0, $item), "", "ajax");
    }
}

if($ta == 'del') {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_store_goods", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除成功"), "", "ajax");
}
