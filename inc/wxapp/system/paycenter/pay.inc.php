<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "统一收银台";

$_config = $_W['we7_hello_banbanjia']['config'];
$id = intval($_GPC['id']);
$type = trim($_GPC['order_type']);
if (empty($id) || empty($type)) {
    imessage(error(-1, "参数错误"), "", "ajax");
}
//订单路由表
$tables_router = array(
    'carry' => array("table" => "hello_banbanjia_carry_order", "cancel_status" => 4, "order_sn" => "order_sn"),
);
$router = $tables_router[$type];
$order = pdo_get($router["table"], array("uniacid" => $_W["uniacid"], "id" => $id));
if (empty($order)) {
    imessage(error(-1, "订单不存在或已删除"), "", "ajax");
}
if (!empty($order["is_pay"])) {
    imessage(error(-1, "该订单已付款"), "", "ajax");
}
if (isset($router["cancel_status"]) && $order["status"] == $router["cancel_status"]) {
    imessage(error(-1, "订单已取消，不能发起支付"), "", "ajax");
}

$order_sn = $order["ordersn"] ? $order["ordersn"] : $order["order_sn"];
$record = pdo_get("hello_banbanjia_paylog", array("uniacid" => $_W["uniacid"], "order_id" => $id, "order_type" => $type, "order_sn" => $order_sn));
if (empty($record)) {
    $record = array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"], "order_sn" => $order_sn, "order_id" => $id, "order_type" => $type, "fee" => $order["final_fee"], "status" => 0, "addtime" => TIMESTAMP);
    pdo_insert("hello_banbanjia_paylog", $record);
    $record["id"] = pdo_insertid();
} else {
    if ($record["status"] == 1) {
        imessage(error(-1, "该订单已支付,请勿重复支付"), "", "ajax");
    }
    if ($order["final_fee"] != $record["fee"]) {
        pdo_update("hello_banbanjia_paylog", array("fee" => $order["final_fee"]), array("id" => $record["id"]));
        $record["fee"] = $order["final_fee"];
    }
}
$logo = $_config["mall"]["logo"];

if ($_GPC['type']) {
    if ($type == 'carry') {
        $config_carry = get_system_config('carry');
        if (is_array($config_carry) && 0 < $config_carry['pay_time_limit']) {
            $data['pay_endtime'] = $order['addtime'] + $config_carry['pay_time_limit'] * 60;
            $data['pay_endtime_cn'] = date("Y/m/d H:i:s", $data['pay_endtime']);
            if ($data['pay_endtime'] < TIMESTAMP) {
                $datap['pay_endtime'] = 0;
            }
        }
    }
}
