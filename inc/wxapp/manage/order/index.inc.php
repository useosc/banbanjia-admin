<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
mload()->lmodel("busorder");
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
    $_W["page"]["title"] = "新建订单";
    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {
        $_GPC["cusid"] = intval($_GPC["cusid"]) ? intval($_GPC["cusid"]) : imessage(error(-1, "客户分类不能为空"), "", "ajax");
        $_GPC["carryprice"] = floatval($_GPC["carryprice"]) ? floatval($_GPC["carryprice"]) : imessage(error(-1, "搬运费不能为空"), "", "ajax");
        $_GPC["unloadprice"] = floatval($_GPC["unloadprice"]) ? floatval($_GPC["unloadprice"]) : imessage(error(-1, "拆卸费不能为空"), "", "ajax");
        $_GPC["packprice"] = floatval($_GPC["carryprice"]) ? floatval($_GPC["carryprice"]) : imessage(error(-1, "搬运费不能为空"), "", "ajax");

        $data = array("uniacid" => $_W["uniacid"], "sid" => $sid, "billno" => date("YmdHis").rand(0,9), "cusid" => $_GPC['cusid'], "create_clerk_id" => $_W['manager']['id'],
        "create_clerk_name" => $_W['manage']['realname'], "transtype" => 1, "totalamount" => 100, 
        "totalcarryprice" => $_GPC['totalcarryprice'],"totalunloadprice" => $_GPC['totalunloadprice'],"totalpackprice" => $_GPC['totalpackprice'],"addtime" => TIMESTAMP,
        "volume" => trim($_GPC['volume']), "displayorder" => intval($_GPC["displayorder"]));
        if (!$id) {
            pdo_insert("hello_banbanjia_store_order", $data);
        } else {
            pdo_update("hello_banbanjia_store_order", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑家具成功"), "", "ajax");
    }
    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_store_order", array("uniacid" => $_W["uniacid"], "id" => $id));
        imessage(error(0, $item), "", "ajax");
    }
}

if($ta == 'del') {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_store_order", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除成功"), "", "ajax");
}
