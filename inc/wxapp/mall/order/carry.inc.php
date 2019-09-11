<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckAuth();
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
mload()->lmodel('order');
if($ta == 'create'){
    $id = intval($_GPC['id']);
    $params = json_decode(htmlspecialchars_decode($_GPC['extra']),true);
    if(empty($params)){
        imessage(error(-1,'参数错误'),'','ajax');
    }
    $start_address = $params['start_address'];
    $end_address = $params['end_address'];
    $order = carry_order_calculate_delivery_fee($id,$params);
    $delivery_info = $order['deliveryInfo'];
    $data = array(
        "uniacid" => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'order_channel' => $_GPC['from'],
        'order_type' => 'free',
        'order_sn' => date("YmdHis") . random(6,true),
        'start_address' => $start_address['address'],
        'start_location_x' => $start_address['location_x'],
        'start_location_y' => $start_address['location_y'],
        'end_address' => $end_address['address'],
        'end_location_x' => $end_address['location_x'],
        'end_location_y' => $end_address['location_y'],
        'service_type' => $order['service_type'],
        'floor' => $order['floor'],
        'stairs_type' => $order['stairs_type'],
        'distance' => $order['distance'],
        'carry_time' => $order['carry_time'],
        'pay_type' => '',
        'is_pay' => 0,
        'carry_fee' => $order['carry_fee'],
        'total_fee' => $order['total_fee'],
        'discount_fee' => $order['discount_fee'],
        'final_fee' => $order['final_fee'],
        'carry_status' => 1,
        'status' => 1,
        'addtime' => TIMESTAMP,
    );
    
}