<?php
defined('IN_IA') or exit('Access Denied');
mload()->lmodel('order.extra');
function domestic_order_calculate($condition = array())
{
    $carry_info = domestic_order_carry_info($condition);
    $carry_fee_info = domestic_order_carry_fee($condition);
    if (is_error($carry_fee_info)) {
        return $carry_fee_info;
    }
    $order = array('carry_fee_info' => $carry_fee_info, 'carry_times' => $carry_info);
    return $order;
}
function domestic_order_carry_info($condition = array())
{
    $carry_times = array();
    $sys_predict_index = 0;

    return $carry_times;
}
function domestic_order_carry_fee($data)
{
    global $_W;
    $start_address = $data['start_address'];
    $end_address = $data['end_address'];
    $goods_volume = floatval($data['goods_volume']);
    $predict_index = intval($data['predict_index']);
    $carry_time = domestic_carry_times();
    $carry_fee_predict_time = $carry_time['times'][$predict_index]['fee'];
    $fees = array();

    $discount_fee = 0; //打折价
    $total_fee = $carry_fee;

    $data = array('goods_volume' => $goods_volume, 'carry_fee' => $carry_fee, 'total_fee' => $total_fee, 'final_fee' => $total_fee - $discount_fee, 'distance' => $distance);
    return $data;
}
