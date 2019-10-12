<?php
// error_reporting(E_ALL ^ E_NOTICE);
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

//计算搬运费 new
function carry_order_calculate_delivery_fee($data, $is_calculate = 0)
{
    global $_W;
    $config_carry = get_system_config('carry');
    $time_fee = $config_carry['time_fee']; //时间费规则

    $estimate_time = $data['estimate_time']; //预估时间

    $time_over_fee = 0;
    if ($estimate_time <= $time_fee[0]['over_time']) {
        $time_fee_total = $time_fee['start_fee'];
    } else {
        foreach ($time_fee as $key => $row) {
            if (strval($key) != 'start_fee') {
                if ($estimate_time > $row['over_time'] && empty($time_fee[$key + 1]['over_time'])) {
                    $time_over = round($estimate_time - $row['over_time'], 2);
                    $time_over_fee += round($row['pre_time_fee'] * $time_over, 2);
                } else if ($estimate_time > $row['over_time'] && $estimate_time <= $time_fee[$key + 1]['over_time']) {
                    $time_over = round($estimate_time - $row['over_time'], 2);
                    $time_over_fee += round($row['pre_time_fee'] * $time_over, 2);
                } else if ($estimate_time > $row['over_time'] && $estimate_time > $time_fee[$key + 1]['over_time']) {
                    $time_over = round($time_fee[$key + 1]['over_time'] - $row['over_time'], 2);
                    $time_over_fee += round($row['pre_time_fee'] * $time_over, 2);
                }
            }
        }
        $time_fee_total = $time_fee['start_fee'] + $time_over_fee;
    }
    $fees['base'] = array('title' => '基础搬运费', 'fee' => $time_fee['start_fee']);
    if (0 < $time_over_fee) {
        $fees[] = array('title' => '时间附加费', 'fee' => $time_over_fee);
    }

    $carry_fee = $time_fee_total;
    $discount_fee = 0; //折扣
    $activityed = carry_order_count_activity($carry_fee, array("redpacket_id" => $data['redpacket_id']));
    if (!empty($activityed['redPacket'])) {
        $redpacket = $activityed['redPacket'];
        $fees[] = array('title' => '红包', 'fee' => 0 - $activityed['redPacket']['discount']);
        $discount_fee += $activityed['total'];
    } else {
        unset($data['redPacket_id']);
    }

    $data = array(
        // "options_fee" => 0,
        "carry_fee" => $carry_fee,
        "time_fee" => $time_over_fee,
        "total_fee" => $carry_fee,
        "discount_fee" => $discount_fee,
        "final_fee" => $carry_fee - $discount_fee,
        "fees" => array_values($fees),
        "activityed" => $activityed,
        "redpacket" => $redpacket,
        "redpacket_id" => $redpacket['id']
    );
    return $data;
}

//计算搬运费bak
function carry_order_calculate_delivery_fee_bak($data, $is_calculate = 0)
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
    if ($order['carry_status'] == 1 && 0 < $_W['deliveryer']['id']) {
        $order["deliveryer_fee"] = carry_order_calculate_deliveryer_fee($order, $_W["deliveryer"]);
        $order["deliveryer_total_fee"] = $order["deliveryer_fee"] + $order["delivery_tips"];
    }
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
    if ($type == 'dispatch') { //派单
        if (empty($order['is_type'])) {
            return error(-1, '订单尚未支付，支付后才能进行调度派单');
        }
        if ($config['dispatch_mode' == 1]) { //抢单
            carry_order_deliveryer_notice($id, "delivery_wait");
        } else {
            if ($config["dispatch_mode"] == 2) { } else { //系统分配

            }
        }
    } else { //订单状态改变
        if ($type == 'pay') {
            carry_order_insert_status_log($id, "pay");
            carry_order_status_notice($id, "pay");
            carry_order_manager_notice($id, "new_delivery");
        } else {
            if ($type == 'cancel') { } else {
                if ($type == 'end') { }
                if ($type == 'carry_assign') {
                    if ($order['status'] == 3) {
                        return error(-1, '系统已完成，不能抢单或分配订单');
                    }
                    if ($order["status"] == 4) {
                        return error(-1, "系统已取消， 不能抢单或分配订单");
                    }
                    if (0 < $order["deliveryer_id"]) {
                        return error(-1, "来迟了, 该订单已被别人接单");
                    }
                    if (empty($extra["deliveryer_id"])) {
                        return error(-1, "搬运工id不存在");
                    }
                    mload()->lmodel("deliveryer");
                    $deliveryer = deliveryer_fetch($extra['deliveryer_id']);
                    if (empty($deliveryer)) {
                        return error(-1, "搬运工不存在");
                    }
                    if (empty($deliveryer)) {
                        return error(-1, "搬运工不存在");
                    }
                    if (0 < $deliveryer["collect_max_carry"]) {
                        $params = array(":uniacid" => $_W["uniacid"], ":deliveryer_id" => $deliveryer["id"]);
                        $num = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_carry_order") . " where uniacid = :uniacid and deliveryer_id = :deliveryer_id and status = 2", $params);
                        $num = intval($num);
                        if ($deliveryer["collect_max_carry"] <= $num) {
                            return error(-1, "每人最多可抢" . $deliveryer["collect_max_carry"] . "个搬运单");
                        }
                    }
                    $update = array("status" => 2, "carry_status" => 2, "deliveryer_id" => $extra["deliveryer_id"], "carry_handle_type" => !empty($extra["carry_handle_type"]) ? $extra["carry_handle_type"] : "wxapp", "carry_assign_time" => TIMESTAMP);
                    $update["deliveryer_fee"] = carry_order_calculate_deliveryer_fee($order, $deliveryer);
                    $update['deliveryer_total_fee'] = $update['deliveryer_fee'] + $order['carry_tips'];
                    pdo_update("hello_banbanjia_carry_order", $update, array("uniacid" => $_W["uniacid"], "id" => $id));

                    // carry_order_update_bill($order['id']);

                    mload()->lmodel('deliveryer');
                    deliveryer_order_num_update($deliveryer['id']);
                    $note = "搬运工: " . $deliveryer['title'] . ", 手机号：" . $deliveryer['mobile'];
                    carry_order_insert_status_log($id, "carry_assign", $note);
                    $remark = array("搬运工: " . $deliveryer["title"], "手机号：" . $deliveryer["mobile"]);

                    // carry_order_status_notice($id, "carry_assign", $remark);

                    return error(0, "抢单成功");
                }
                if ($type == 'carry_indoor') { //确认上门
                    if ($order["status"] == 3) {
                        return error(-1, "系统已完成， 不能变更状态");
                    }
                    if ($order["status"] == 4) {
                        return error(-1, "系统已取消， 不能变更状态");
                    }
                    if (empty($extra["deliveryer_id"])) {
                        return error(-1, "搬运工不存在");
                    }
                    $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "id" => $extra["deliveryer_id"]));
                    if (empty($deliveryer)) {
                        return error(-1, "搬运工不存在");
                    }
                    if ($deliveryer["status"] != 1) {
                        return error(-1, "搬运工已被删除");
                    }
                    if ($order["deliveryer_id"] != $deliveryer["id"]) {
                        return error(-1, "该订单不是您搬运，不能确认取货");
                    }
                    $update = array("status" => 2, "carry_status" => 3, "carry_indoor_time" => TIMESTAMP, "carry_handle_type" => !empty($extra["carry_handle_type"]) ? $extra["carry_handle_type"] : "wxapp");
                    pdo_update("hello_banbanjia_carry_order", $update, array("uniacid" => $_W["uniacid"], "id" => $id));
                    $note = "搬运工：" . $deliveryer["title"] . ", 手机号：" . $deliveryer["mobile"] . "已上门";
                    carry_order_insert_status_log($id, "carry_indoor", $note);
                    // carry_order_status_notice($id, "carry_indoor");
                    return error(0, "确认上门成功");
                }
                if ($type == "carry_success") {
                    if ($order["status"] == 3) {
                        return error(-1, "系统已完成， 不能变更状态");
                    }
                    if ($order["status"] == 4) {
                        return error(-1, "系统已取消， 不能变更状态");
                    }
                    if (empty($extra["deliveryer_id"])) {
                        return error(-1, "搬运工不存在");
                    }
                    $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "id" => $extra["deliveryer_id"]));
                    if (empty($deliveryer)) {
                        return error(-1, "搬运工不存在");
                    }
                    if ($deliveryer["status"] != 1) {
                        return error(-1, "搬运工已被删除");
                    }
                    if ($order["deliveryer_id"] != $deliveryer["id"]) {
                        return error(-1, "该订单不是您搬运，不能确认完成");
                    }
                    // if ($config["verification_code"] == 1) {
                    //     if (empty($extra["code"])) {
                    //         return error(-1, "收货码不能为空");
                    //     }
                    //     if ($extra["code"] != $order["code"]) {
                    //         return error(-1, "收货码有误");
                    //     }
                    // }
                    $update = array("status" => 3, "carry_status" => 4, "carry_success_time" => TIMESTAMP, "carry_success_location_x" => $deliveryer["location_x"], "carry_success_location_y" => $deliveryer["location_y"]);
                    pdo_update("hello_banbanjia_carry_order", $update, array("uniacid" => $_W["uniacid"], "id" => $id));
                    mload()->lmodel("deliveryer");
                    deliveryer_order_num_update($deliveryer["id"]);
                    $total_deliveryer_fee = $order["deliveryer_fee"] + $order["delivery_tips"];
                    // if (0 < $total_deliveryer_fee) { //更新积分
                    //     mload()->lmodel("deliveryer");
                    //     deliveryer_update_credit2($order["deliveryer_id"], $total_deliveryer_fee, 1, $id, "", "carry");
                    // }
                    // if (0 < $order["agentid"]) { //代理
                    //     $remark = "搬运订单,id:" . $order["id"];
                    //     agent_update_account($order["agentid"], $order["agent_final_fee"], 1, $order["id"], $remark, "carry");
                    // }
                    carry_order_insert_status_log($id, "end");
                    // carry_order_status_notice($id, "end");
                    return error(0, "确认送达成功");
                }
                if ($type == 'end') { //已完成
                    if ($order["status"] == 3) {
                        return error(-1, "系统已完成， 请勿重复操作");
                    }
                    if ($order["status"] == 4) {
                        return error(-1, "系统已取消， 不能在进行其他操作");
                    }
                    $update = array("status" => 3, "carry_status" => 4, "carry_success_time" => TIMESTAMP);
                    if (0 < $order["deliveryer_id"]) {
                        $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "id" => $order["deliveryer_id"]));
                        if (!empty($deliveryer)) {
                            mload()->lmodel("deliveryer");
                            deliveryer_order_num_update($deliveryer["id"]);
                            $update["carry_success_location_x"] = $deliveryer["location_x"];
                            $update["carry_success_location_y"] = $deliveryer["location_y"];
                        }
                    }
                    pdo_update("hello_banbanjia_carry_order", $update, array("uniacid" => $_W['uniacid'], 'id' => $id));
                    $total_deliveryer_fee = $order["deliveryer_fee"] + $order["carry_tips"];
                    if (0 < $total_deliveryer_fee) {
                        mload()->lmodel("deliveryer");
                        deliveryer_update_credit2($order["deliveryer_id"], $total_deliveryer_fee, 1, $id, "", "carry");
                    }
                    $credit1_config = $config["credit"]["credit1"];
                    if (!empty($credit1_config) && $credit1_config["status"] == 1 && 0 < $credit1_config["grant_num"] && 0 < $order["uid"]) {
                        $credit1 = $credit1_config["grant_num"];
                        if ($credit1_config["grant_type"] == 2) {
                            $credit1 = round($order["final_fee"] * $credit1_config["grant_num"], 2);
                        }
                        if (0 < $credit1) {
                            mload()->lmodel("member");
                            $result = member_credit_update($order["uid"], "credit1", $credit1, array(0, "搬运订单完成, 赠送:" . $credit1 . "积分"));
                            if (is_error($result)) {
                                slog("credit1Update", "搬运下单送积分-order_id:" . $order["id"], array("order_id" => $order["id"], "uid" => $order["uid"], "credit_type" => "credit1"), $result["message"]);
                            }
                        }
                    }
                    // carry_order_insert_status_log($id, "end", $extra["note"]);
                    // carry_order_status_notice($id, "end", $extra["note"]);
                    return error(0,'订单完成成功');
 
                }
            }
        }
    }

    return true;
}

//计算搬运工费用
function carry_order_calculate_deliveryer_fee($order, $deliveryerOrid = 0)
{
    global $_W;
    $deliveryer = $deliveryerOrid;
    if (!is_array($deliveryer)) {
        mload()->lmodel("deliveryer");
        $deliveryer = deliveryer_fetch($deliveryerOrid);
    }
    if (empty($deliveryer)) {
        return 0;
    }
    $config_carry = get_deliveryer_feerate($deliveryer, 'carry');
    $plateform_carry_fee = floatval($config_carry['deliveryer_fee']);

    if ($config_carry['deliveryer_fee_type'] == 4) {
        $plateform_carry_fee = round($order['final_fee'] * $config_carry['deliveryer_fee'] / 100, 2);
    }

    return floatval($plateform_carry_fee);
}

//搬运工费率计算
function get_deliveryer_feerate($deliveryer, $type = '')
{
    $carry_fee = iunserializer($deliveryer['fee_carry']);
    if (!empty($carry_fee[$type])) {
        return $carry_fee[$type];
    }
    return array();
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
// 搬运订单更新使用红包
function carry_order_insert_discount($id, $discount_data)
{
    global $_W;
    if (empty($discount_data)) {
        return false;
    }
    if (!empty($discount_data["redPacket"])) {
        pdo_update("hello_banbanjia_activity_redpacket_record", array("status" => 2, "usetime" => TIMESTAMP, "order_id" => $id), array("uniacid" => $_W["uniacid"], "id" => $discount_data["redPacket"]["redPacket_id"]));
    }
    // foreach ($discount_data as $data) {
    //     $insert = array("uniacid" => $_W["uniacid"], "oid" => $id, "type" => $data["type"], "name" => $data["name"], "icon" => $data["icon"], "note" => $data["text"], "fee" => $data["value"], "store_discount_fee" => floatval($data["store_discount_fee"]), "agent_discount_fee" => floatval($data["agent_discount_fee"]), "plateform_discount_fee" => floatval($data["plateform_discount_fee"]));
    //     pdo_insert("hello_banbanjia_carry_order_discount", $insert);
    // }
    return true;
}
// 搬运订单通知搬运工
function carry_order_deliveryer_notice($id, $type, $deliveryer_id = 0, $note = "")
{
    global $_W;
    $order = carry_order_fetch($id);
    if (empty($order)) {
        return error(-1, "订单不存在或已删除");
    }
    mload()->lmodel('deliveryer');
    if (empty($deliveryer_id)) {
        $filter = array("order_type" => "is_carry", "over_max_collect_show" => 0);
        $deliveryers = deliveryer_fetchall(0, $filter);
        if (empty($deliveryers)) {
            carry_order_manager_notice($order["id"], "no_working_deliveryer");
            return false;
        }
    } else {
        $deliveryer = deliveryer_fetch($deliveryer_id);
    }
    $account = $order["uniacid"];
    $channel_notice = "wechat";
}
// 搬运订单状态更新通知
function carry_order_status_notice($id, $status, $note = "")
{
    global $_W;
    $status_arr = array("pay", "carry_assign", "carry_indoor", "end", "cancel", "carry_notice");
    if (!in_array($status, $status_arr)) {
        return false;
    }
    $type = $status;
    $order = carry_order_fetch($id);
    if (!empty($order['openid'])) {
        $config_wxapp_basic = $_W['we7_hello_banbanjia']['config']['wxapp']['basic'];
        $order_channel = $order['order_channel']; //wxapp wap pc
        if ($order_channel == 'wxapp') {
            mload()->lmodel('member');
            $openid = member_wxapp2openid($order['openid']);
            if (!empty($openid)) {
                $order_channel = "wap";
                $order["openid"] = $openid;
            }
        }
        $acc = TyAccount::create($order['acid'], $order_channel);
        $channel_notice = "wechat";

        if ($order_channel == 'wxapp') {
            $channel_notice = 'wxapp';
            $send = array(
                "keyword1" => array("value" => "搬运单", "color" => "#ff510"),
                "keyword2" => array("value" => $order["order_type_cn"], "color" => "#ff510"),
                "keyword3" => array("value" => $order["status_cn"], "color" => "#ff510"),
                "keyword4" => array("value" => $order["accept_username"], "color" => "#ff510"),
                "keyword5" => array("value" => $order["accept_mobile"], "color" => "#ff510"),
                "keyword6" => array("value" => date("Y-m-d H:i"), "color" => "#ff510"),
                "keyword7" => array("value" => $order["final_fee"], "color" => "#ff510"),
                "keyword8" => array("value" => $order["order_sn"], "color" => "#ff510")
            );
            $public_tpl = $_W['we7_hello_banbanjia']['config']['wxapp']['wxtemplate']['public_tpl'];
            $form_id = $order['data']['formId'];
            $form_type = "formId";
            if (empty($form_id) && 0 < $order['data']['prepay_times']) {
                $form_type = 'prepayId';
                $form_id = $order['data']['prepay_id'];
            }
            // if(!empty($form_id)){
            //     if()
            // }
        }
    }

    return true;
}

// 搬运订单通知管理员
function carry_order_manager_notice($order_id, $type, $note = "")
{
    global $_W;
    $maneger = $_W["we7_hello_banbanjia"]["config"]["manager"];
    if (empty($maneger)) {
        return error(-1, "管理员信息不完善");
    }
    $order = carry_order_fetch($order_id);
    if (empty($order)) {
        return error(-1, "订单不存在或已经删除");
    }
    $acc = WeAccount::create($order['acid']);
    if ($type == "new_carry") {
        $title = "平台有新的搬运订单，请尽快调度处理";
        $remark = array("订单类型: " . $order["order_type_cn"],  "总金额: " . $order["total_fee"], "支付方式: " . $order["pay_type_cn"], "支付时间: " . date("Y-m-d H: i", $order["paytime"]));
    } else {
        if ($type == "dispatch_error") {
            $title = "平台有新的搬运订单，系统自动调度失败，请登录后台人工调度";
            $remark = array("订单类型: " . $order["order_type_cn"], "总金额: " . $order["total_fee"]);
        } else {
            if ($type == "no_working_deliveryer") {
                $title = "平台有新的待搬运订单,但没有接单中的搬运工,请尽快协调";
                $remark = array("订单类型: 搬运订单");
            }
        }
    }
    if (!empty($note)) {
        if (!is_array($note)) {
            $remark[] = $note;
        } else {
            $remark[] = implode("\n", $note);
        }
    }
    if (!empty($end_remark)) {
        $remark[] = $end_remark;
    }
    $remark = implode("\n", $remark);
    $send = tpl_format($title, $order["order_sn"], $order["status_cn"], $remark);
    $status = $acc->sendTplNotice($maneger["openid"], $_W["we7_hello_banbanjia"]["config"]["notice"]["wechat"]["public_tpl"], $send);
    if (is_error($status)) {
        slog("wxtplNotice", "搬运订单通知平台管理员抢单", $send, $status["message"]);
    }
    return $status;
}

//插入搬运订单状态
function carry_order_insert_status_log($id, $type, $note = "", $extra = array())
{
    global $_W;
    if (empty($type)) {
        return false;
    }
    $config = $_W['we7_hello_banbanjia']['config'];
    $order = carry_order_fetch($id);
    $notes = array(
        "place_order" => array("status" => 1, "title" => "订单提交成功", "note" => "单号：" . $order['order_sn'], "ext" => array(array("key" => "pay_time_limit", "title" => "待支付", "note" => "请在订单提交后" . $config['pay_time_limit'] . "分钟内完成支付"))),
        "pay" => array("status" => 2, "title" => "订单已支付", "note" => "支付成功.付款时间:" . date("Y-m-d H:i:s", $order['paytime']), "ext" => array(array("key" => "handle_time_limit", "title" => "待接单", "note" => "超出" . $config['handle_time_limit'] . "分钟未接单，平台将自动取消订单"))),
        "carry_assign" => array("status" => 3, "title" => "已接单", "note" => ""),
        "carry_indoor" => array("status" => 4, "title" => "已上门", "note" => ""),
        "end" => array("status" => 5, "title" => "订单已完成", "note" => "任何意见和吐槽,都欢迎联系我们"),
        "cancel" => array("status" => 6, "title" => "订单已取消", "note" => ""),
        "carry_transfer" => array("status" => 7, "title" => "搬运工申请转单", "note" => ""),
        "direct_transfer" => array("status" => 8, "title" => "搬运工发起定向转单申请", "note" => ""),
        "direct_transfer_agree" => array("status" => 9, "title" => "搬运工同意接受转单", "note" => ""),
        "direct_transfer_refuse" => array("status" => 10, "title" => "搬运工拒绝接受转单", "note" => "")
    );
    $title = $notes[$type]['title'];
    $note = $note ? $note : $notes[$type]['note'];
    $role = !empty($extra['role']) ? $extra['role'] : $_W['role'];
    $role_cn = !empty($extra["role_cn"]) ? $extra["role_cn"] : $_W["role_cn"];
    $data = array("uniacid" => $_W["uniacid"], "oid" => $id, "status" => $notes[$type]["status"], "role" => $role, "role_cn" => $role_cn, "type" => $type, "title" => $title, "note" => $note, "addtime" => TIMESTAMP);
    pdo_insert("hello_banbanjia_carry_order_status_log", $data);
    if (!empty($notes[$type]["ext"])) {
        foreach ($notes[$type]["ext"] as $val) {
            if ($val["key"] == "pay_time_limit" && !$config["pay_time_limit"]) {
                unset($val["note"]);
            }
            if ($val["key"] == "handle_time_limit" && !$config["handle_time_limit"]) {
                unset($val["note"]);
            }
            $data = array("uniacid" => $_W["uniacid"], "oid" => $id, "title" => $val["title"], "note" => $val["note"], "addtime" => TIMESTAMP);
            pdo_insert("hello_banbanjia_carry_order_status_log", $data);
        }
    }
    return true;
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
