<?php
defined('IN_IA') or exit('Access Denied');
mload()->lmodel('order.extra');
// function domestic_order_calculate($condition = array())
// {
//     $carry_info = domestic_order_carry_info($condition);
//     $carry_fee_info = domestic_order_carry_fee($condition);
//     if (is_error($carry_fee_info)) {
//         return $carry_fee_info;
//     }
//     $order = array('carry_fee_info' => $carry_fee_info, 'carry_times' => $carry_info);
//     return $order;
// }
// function domestic_order_carry_info($condition = array())
// {
//     $carry_times = array();
//     $sys_predict_index = 0;

//     return $carry_times;
// }
// function domestic_order_carry_fee($data)
// {
//     global $_W;
//     $start_address = $data['start_address'];
//     $end_address = $data['end_address'];
//     $goods_volume = floatval($data['goods_volume']);
//     $predict_index = intval($data['predict_index']);
//     $carry_time = domestic_carry_times();
//     $carry_fee_predict_time = $carry_time['times'][$predict_index]['fee'];
//     $fees = array();

//     $discount_fee = 0; //打折价
//     $total_fee = $carry_fee;

//     $data = array('goods_volume' => $goods_volume, 'carry_fee' => $carry_fee, 'total_fee' => $total_fee, 'final_fee' => $total_fee - $discount_fee, 'distance' => $distance);
//     return $data;
// }


//计算搬运费
function carry_order_calculate_delivery_fee($data, $is_calculate = 0)
{
    global $_W;
    $config_carry = get_system_config('carry');
    $goods_volume = floatval($data['goods_volume']);

    $km_fee = $config_carry['km_fee']; //物流费
    $volume_fee = $config_carry['volume_fee']; //包干服务费

    // if (!empty($data['start_address']['location_x']) && !empty($data['end_address']['location_y'])) {
    //     $origins = array($data['start_address']['location_y'], $data['start_address']['location_x']);
    //     $destionation = array($data['end_address']['location_y'], $data['start_address']['location_x']);
    //     // $distance_type = array("riding" => 2,"driving" => 1,"line" => 0,"walking" =>3); //默认直线
    //     $distance = calculate_distance($origins, $destionation);
    // }
    if (!empty($data['start_location_x']) && !empty($data['end_location_y'])) {
        $origins = array($data['start_location_y'], $data['start_location_x']);
        $destionation = array($data['end_location_y'], $data['end_location_x']);
        $distance = calculate_distance($origins, $destionation);
    }

    $distance_over_fee = 0;
    if ($distance <= $km_fee[0]['over_km']) {
        $distance_fee = $km_fee['start_fee'];
    } else {
        foreach ($km_fee as $key => $row) {
            if (strval($key) != 'start_fee') {
                if ($distance > $row['over_km'] && empty($km_fee[$key + 1]['over_km'])) {
                    $distance_over = round($distance - $row['over_km'], 2);
                    $distance_over_fee += round($row['pre_km_fee'] * $distance_over, 2);
                } else if ($distance > $row['over_km'] && $distance <= $km_fee[$key + 1]['over_km']) {
                    $distance_over = round($distance - $row['over_km'], 2);
                    $distance_over_fee += round($row['pre_km_fee'] * $distance_over, 2);
                } else if ($distance > $row['over_km'] && $distance > $km_fee[$key + 1]['over_km']) {
                    $distance_over = round($km_fee[$key + 1]['over_km'] - $row['over_km'], 2);
                    $distance_over_fee += round($row['pre_km_fee'] * $distance_over, 2);
                }
            }
        }
        $distance_fee = $km_fee['start_fee'] + $distance_over_fee;
    }
    $fee['base'] = array('title' => '基础搬运费', 'fee' => $km_fee['start_fee']);
    if (0 < $distance_over_fee) {
        $fees[] = array('title' => '距离附加费', 'fee' => $distance_over_fee);
    }

    $volume_over_fee = 0;
    if ($goods_volume <= $volume_fee[0]['over_cube']) {
        $gvolume_fee = 0;
    } else {
        foreach ($volume_fee as $key => $row) {
            if (strval($key) != 'start_fee') {
                if ($goods_volume > $row['over_cube'] && empty($volume_fee[$key + 1]['over_cube'])) {
                    $volume_over = round($goods_volume - $row['over_cube'], 2);
                    $volume_over_fee += round($row['pre_cube_fee'] * $volume_over, 2);
                } else if ($goods_volume > $row['over_cube'] && $goods_volume <= $volume_fee[$key + 1]['over_cube']) {
                    $volume_over = round($goods_volume - $row['over_cube'], 2);
                    $volume_over_fee += round($row['pre_cube_fee'] * $volume_over, 2);
                } else if ($goods_volume > $row['over_cube'] && $goods_volume > $volume_fee[$key + 1]['over_cube']) {
                    $volume_over = round($volume_fee[$key + 1]['over_cube'] - $row['over_cube'], 2);
                    $volume_over_fee += round($row['pre_cube_fee'] * $volume_over, 2);
                }
            }
        }
        $gvolume_fee = $volume_over_fee;
    }
    if (0 < $gvolume_fee) {
        $fees[] = array('title' => '包干服务费', 'fee' => $gvolume_fee);
    }
    // $fees = array_merge($fees);
    $discount_fee = 0; //折扣
    // $carry_fee = $distance_fee + $gvolume_fee;
    $activityed = carry_order_count_activity($distance_fee, array("redpacket_id" => $data['redpacket_id']));
    if (!empty($activityed['redPacket'])) {
        $redpacket = $activityed['redPacket'];
        $fees[] = array('title' => '红包', 'fee' => 0 - $activityed['redPacket']['discount']);
        $discount_fee += $activityed['total'];
    } else {
        unset($data['redPacket_id']);
    }

    $service_fee = 6;
    $data = array(
        "distance" => $distance,
        "km_fee" => $distance_fee,
        "volume_fee" => $gvolume_fee,
        "service_fee" => $service_fee,
        "options_fee" => 0,
        "total_fee" => $discount_fee + $gvolume_fee + $service_fee,
        "discount_fee" => $discount_fee,
        "final_fee" => $discount_fee + $gvolume_fee + $service_fee - $discount_fee,
        "fees" => array_values($fees),
        "activityed" => $activityed,
        "redpacket" => $redpacket,
        "redpacket_id" => $redpacket['id']
        // "redpacket" => $redpacket
    );
    // $data = array(
    // "service_type" => "free",
    //     "floor" => 5,
    //     "stairs_type" => "stairs",
    //     "distance" => 6,
    //     "goods_volume" => 10,
    //     "km_fee" => 10,
    //     "volume_fee" => 8,
    //     "service_fee" => 5,
    //     "options_fee" => 20,
    //     "total_fee" => 43,
    //     "discount_fee" => 20,
    //     "final_fee" => 23
    // );
    return $data;
}

//搬运订单接单
function carry_order_fetch($id)
{
    global $_W;
    $id = intval($id);
    $order = pdo_fetch("SELECT * FROM " . tablename("hello_banbanjia_carry_order") . " WHERE uniacid = :aid AND id = :id", array(":aid" => $_W["uniacid"], ":id" => $id));
    if (empty($order)) {
        return false;
    }
    if ($order['carry_status'] == 1 && 0 < $_W['deliveryer']['id']) { }
    $carry_status = carry_order_delivery_status();
    $order_status = carry_order_status();
    $pay_types = order_pay_types();
    $order['status_cn'] = $order_status[$order['status']];
    $order['carry_status_cn'] = $carry_status[$order['carry_status']];
    // $order_types = carry_types();
    if (empty($order["is_pay"])) {
        $order["pay_type_cn"] = "未支付";
    } else {
        $order["pay_type_cn"] = !empty($pay_types[$order["pay_type"]]) ? $pay_types[$order["pay_type"]] : "其他支付方式";
    }
    if (empty($order['carry_time'])) {
        $order['carry_time'] = '立即搬运';
    }
    if (0 < $order['refund_status']) {
        $refund_channel = order_refund_channel();
        $refund_status = order_refund_status();
        $order['refund_status_cn'] = $refund_status[$order['refund_status']];
        $order['refund_channel_cn'] = $refund_channel[$order['refund_channel']];
    }
    // $order["data"] = iunserializer($order["data"]);
    $order['addtime_cn'] = date("Y-m-d H:i", $order['addtime']);
    return $order;
}
//搬运订单状态更新
function carry_order_status_update($id, $type, $extra = array())
{
    global $_W;
    $order = carry_order_fetch($id);
    if (empty($order)) {
        return error(-1, "订单不存在或已删除");
    }
    $config = get_system_config("carry");
    if ($type == 'dispatch') {
        if (empty($order['is_type'])) {
            return error(-1, '订单尚未支付，支付后才能进行调度派单');
        }
        if ($config['dispatch_mode' == 1]) {
            carry_order_deliveryer_notice($id, "delivery_wait");
        }
    } else {
        if ($type == 'pay') { } else {
            if ($type == 'cancel') { } else {
                if ($type == 'end') { }
                if ($type == "carry_assign") {
                    if ($order['status'] == 3) {
                        return error(-1, "系统已完成，不能抢单或分配订单");
                    }
                    if ($order['status'] == 4) {
                        return error(-1, '系统已取消，不能抢单或分配订单');
                    }
                    if (0 < $order['deliveryer_id']) {
                        return error(-1, '来迟了，该订单已被别人接单');
                    }
                    if (empty($extra['deliveryer_id'])) {
                        return error(-1, '搬运工id不存在');
                    }
                    mload()->lmodel('deliveryer');
                    $deliveryer = deliveryer_fetch($extra['deliveryer_id']);
                    if (empty($deliveryer)) {
                        return error(-1, "配送员不存在");
                    }
                    if (0 < $deliveryer['collect_max_carry']) {
                        $params = array(":uniacid" => $_W['uniacid'], ":deliveryer_id" => $deliveryer['id']);
                        $num = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_carry_order") . " where uniacid = :uniacid and deluveryer_id = :deliveryer_id and status = 2", $params);
                        $num = intval($num);
                        if ($deliveryer["collect_max_carry"] <= $num) {
                            return error(-1, "每人最多可同时抢" . $deliveryer["collect_max_carry"] . "个搬运单");
                        }
                    }
                    $update = array("status" => 2, "carry_status" => 2, "deliveryer_id" => $extra["deliveryer_id"], "carry_handle_type" => !empty($extra['carry_handle_type']) ? $extra['carry_handle_type'] : "wechat", "carry_assign_time" => TIMESTAMP);
                    // $update['']
                    // pdo_update("hello_banbanjia_carry_order",$update,array("uniacid" => $_W['uniacid'],"id"=>$id));


                    return error(0, "抢单成功");
                }
            }
        }
    }
}
//搬运订单计算活动
function carry_order_count_activity($delivery_fee = 0, $data = array())
{
    $activityed = array('list' => '', 'total' => 0, 'activity' => 0, 'token' => 0);
    if (!empty($data['redpacket_id'])) {
        mload()->lmodel('redPacket');
        $redpacket = redPacket_available_check($data['redpacket_id'], $delivery_fee, array('scene' => 'carry'));
        if (!is_error($redpacket)) {
            $activityed["list"]["redPacket"] = array("text" => "-￥" . $redpacket["discount"], "value" => $redpacket["discount"], "type" => "redPacket", "name" => "平台红包优惠", "icon" => "redPacket_b.png", "redPacket_id" => $redpacket["id"], "plateform_discount_fee" => $redpacket["discount"], "agent_discount_fee" => 0, "store_discount_fee" => 0);
            $activityed["redPacket"] = $redpacket;
            $activityed["total"] += $redpacket["discount"];
            $activityed["activity"] += $redpacket["discount"];
            $activityed["store_discount_fee"] += 0;
            $activityed["agent_discount_fee"] += 0;
            $activityed["plateform_discount_fee"] += $redpacket["discount"];
        }
    }
    return $activityed;
}
//搬运订单状态查询
function carry_order_fetch_status_log($id)
{
    global $_W;
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_carry_order_status_log") . " WHERE uniacid = :uniacid and oid = :oid order by id asc", array(":uniacid" => $_W['uniacid'], ":oid" => $id), "id");
    return $data;
}
//搬运订单退款查询
function carry_order_fetch_refund_status_log($id)
{
    global $_W;
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_order_refund_log") . " WHERE uniacid = :uniacid and oid = :oid and order_type = :order_type order by id asc", array(":uniacid" => $_W["uniacid"], ":oid" => $id, ":order_type" => "carry"), "id");
    return $data;
}

//搬运订单类型
function carry_order_types()
{
    $data = array(
        'free' => '搬移',
        'share' => '搬运'
    );
    return $data;
}
//搬运订单搬运状态
function carry_order_delivery_status()
{
    $data = array(
        "1" => "待接单",
        "2" => "待上门",
        "3" => "搬运中",
        "4" => "已完成"
    );
    return $data;
}
//搬运订单状态
function carry_order_status()
{
    $data = array(
        "所有", "待接单", "正在进行中", "已完成", "已取消"
    );
    return $data;
}
//订单退款渠道
function order_refund_channel()
{
    $refund_channel = array(
        "ORIGINAL" => "原路返回",
        "BALANCE" => "退回余额"
    );
    return $refund_channel;
}
//订单支付类型
function order_pay_types()
{
    $pay_types = array(
        "" => "未支付",
        "alipay" => "支付宝",
        "wechat" => "微信支付",
        "credit" => "余额支付",
        "delivery" => "货到付款",
        "cash" => "现金",
        "peerpay" => "找人代付"
    );
    return $pay_types;
}
//订单退款状态
function order_refund_status()
{
    $refund_status = array(
        "1" => "退款申请中",
        "2" => "退款处理中",
        "3" => "退款成功",
        "4" => "退款失败",
        "5" => "退款状态未确定",
        "6" => "退款被驳回"
    );
    return $refund_status;
}
//下单渠道
function order_channel($channel = "", $all = false)
{
    $data = array(
        "web" => "PC下单",
        "wxapp" => "小程序下单",
        "wap" => "移动端下单",
    );
    if (!empty($channel)) {
        $data = $data[$channel];
        if (empty($all)) {
            $data = $data["text"];
        }
    }
    return $data;
}
