<?php
defined("IN_IA") or exit("Access Denied");

// function domestic_order_fetch($id, $oauth = false)
// {
//     global $_W;
//     $id = intval($id);
//     $condition = " where uniacid = :uniacid and id = :id";
//     $params = array(":uniacid" => $_W['uniacid'], ":id" => $id);
//     if ($oauth) {
//         $condition .= " and uid = :uid";
//         $params[":uid"] = $_W['member']['uid'];
//     }
//     $order = pdo_fetch("SELECT * FROM " . tablename("hello_banbanjia_domestic_order") . $condition, $params);
//     if (empty($order)) {
//         return false;
//     }
//     if($order['order_type'] != 'free' && $order['carry_status'] == 1 && 0 < $_W['deliveryer']['id']){
//         $order['carry_fee'] = domestic_order_calculate_carry_fee($order,$_W['deliveryer']);
//     }
//     $carry_status = domestic_order_carry_status();
//     $order_status = domestic_order_status();
//     $pay_types = order_pay_types();
    

//     return $order;
// }
// function domestic_order_carry_status() //搬运状态
// {
//     $data = array("1" => "待接单", "2" => "待抵达", "3" => "搬运中", "4" => "已完成");
//     return $data;
// }
// function domestic_order_status() //订单状态
// {
//     $data = array('所有','待接单','正在进行中','已完成','已取消');
//     return $data;
// }
// function order_pay_types() //订单支付类型
// {
//     $pay_types = array(''=>'未支付','alipay'=>'支付宝','wechat'=>'微信支付');
//     return $pay_types;
// }


