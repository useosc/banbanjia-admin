<?php

defined("IN_IA") or exit("Access Denied");

//是否超过搬运范围
function is_in_carry_radius($lnglat)
{
    global $_W;
}
//计算搬运费
function carry_order_calculate_delivery_fee($data, $is_calculate = 0)
{ 
    global $_W;
    $start_address = $data['start_address'];
    $end_address = $data['end_address'];
    $goods_volume = floatval($data['goods_volume']);
    
}
