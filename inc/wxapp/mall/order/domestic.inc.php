<?php

defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckAuth();
mload()->lmodel('order');
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if($ta == 'list'){  //订单列表

}

if ($ta == 'create') { //创建订单
    $data = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'order_type' => $_GPC['order_type'] ? $_GPC['order_type'] : imessage(error(-1,''),'订单类型不能为空','ajax'),
        'order_channel' => $_GPC['from'] ? $_GPC['from'] : imessage(error(-1,''),'设备类型不能为空','ajax'),
        'goods_volume' => $_GPC['goods_volume'] ? $_GPC['goods_volume'] : imessage(error(-1,''),'物品体积不能为空','ajax'),
        'carry_time' => $_GPC['carry_time'],
        'start_address' => $_GPC['start_address'] ? $_GPC['start_address'] : imessage(error(-1,''),'起始地址不能为空','ajax'),
        'end_address' => $_GPC['end_address'],
        'start_location_x' => $_GPC['start_location_x'],
        'start_location_y' => $_GPC['start_location_y'],
        'end_location_x' => $_GPC['end_location_x'],
        'end_location_y' => $_GPC['end_location_y'],
        'service_type' => $_GPC['service_type'],
        'floor' => $_GPC['floor'],
        'stairs_type' => $_GPC['stairs_type'],
        'carry_time' => $_GPC['carry_time'],
        'addtime' => time(),
        'remark' => $_GPC['remark']
    );
    $order = domestic_order_calculate($data);
    pdo_insert('hello_banbanjia_domestic_order',$data);
    $orderid = pdo_insertid();
    domestic_order_insert_status_log($orderid,"place_order");
    imessage(error(0,$orderid),'','ajax');
}else{
    if($ta == 'detail'){ //订单详情
        $id = intval($_GPC['id']);
        $order = domestic_order_fetch($id);
        if (empty($order)) {
            imessage("订单不存在或已删除", "", "error");
        }
      
    }
}
