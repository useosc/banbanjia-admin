<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckAuth();
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
mload()->lmodel('order');
if($ta == 'create'){
    // $id = intval($_GPC['id']);
    $params = json_decode(htmlspecialchars_decode($_GPC['extra']),true);
    if(empty($params)){
        imessage(error(-1,'参数错误'),'','ajax');
    }
    $order = carry_order_calculate_delivery_fee($params);
    // $delivery_info = $order['deliveryInfo'];
    $data = array(
        "uniacid" => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'order_type' => $params['order_type'],
        'order_sn' => date("YmdHis") . random(6,true),
        'start_address' => $params['start_address'],
        'start_location_x' => $params['start_location_x'],
        'start_location_y' => $params['start_location_y'],
        'estimate_time' => $params['estimate_time'],
        'carry_time' => $params['carry_time'],
        'options_fee' => $order['options_fee'],
        'pay_type' => '',
        'is_pay' => 0,
        'carry_fee' => $order['carry_fee'],
        'time_fee' => $order['time_fee'],
        'total_fee' => $order['total_fee'],
        'discount_fee' => $order['discount_fee'],
        'final_fee' => $order['final_fee'],
        'remark' => $params['remark'],
        'deliveryer_fee' => 0,
        'deliveryer_total_fee' => 0,
        'order_channel' => $_GPC['from'],
        'carry_status' => 1,
        'status' => 1,
        'addtime' => TIMESTAMP,
    );
    
    $data["spreadbalance"] = 1;
    // if (check_plugin_permit("spread")) {
    //     pload()->model("spread");
    //     $data = order_spread_commission_calculate("carry", $data);
    // }

    // $data['data'] = iserializer($data['data']);
    pdo_insert('hello_banbanjia_carry_order',$data);
    $orderid = pdo_insertid();
    // 隐私号
    // carry_order_insert_status_log($orderid,"place_order");
    // carry_order_insert_discount($orderid,$order['activityed']['list']);
    // carry_order_insert_status_log($orderid,"place_order");
    imessage(error(0,$orderid),'','ajax');
}
