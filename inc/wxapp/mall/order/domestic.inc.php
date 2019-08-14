<?php

defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckAuth();
mload()->lmodel('domestic_order');
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if ($ta == 'create') {
    $data = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'order_type' => $_GPC['order_type'],
        'order_channel' => $_GPC['order_channel'],
        'goods_volume' => $_GPC['goods_volume'],
        'start_address' => $_GPC['start_address'],
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
    pdo_insert('hello_banbanjia_domestic_order',$data);
    $orderid = pdo_insertid();
    imessage(error(0,$orderid),'','ajax');
}
