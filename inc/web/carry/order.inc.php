<?php
error_reporting(E_ALL^E_NOTICE);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('deliveryer');
mload()->lmodel('order');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "订单列表";
    //订单统计
    $condition = " where uniacid = :uniacid";
    $params[":uniacid"] = $_W["uniacid"];
    $filter_type = trim($_GPC["filter_type"]) ? trim($_GPC["filter_type"]) : "process";
    $status = intval($_GPC['status']);
    if (0 < $status) {
        $condition .= " AND status = :status";
        $params[":status"] = $status;
    } else {
        if ($filter_type == "process") {
            $condition .= " AND status >= 1 AND status <= 2";
        }
    }
    $re_status = intval($_GPC["refund_status"]);
    if (0 < $re_status) {
        $condition .= " AND refund_status = :refund_status";
        $params[":refund_status"] = $re_status;
    }
    $is_pay = isset($_GPC['is_pay']) ? intval($_GPC['is_pay']) : 1;
    if (-1 < $is_pay) {
        $condition .= " AND is_pay = :is_pay";
        $params[":is_pay"] = $is_pay;
    }
    $pay_type = trim($_GPC["pay_type"]);
    if (!empty($pay_type)) {
        $condition .= " AND is_pay = 1 AND pay_type = :pay_type";
        $params[":pay_type"] = $pay_type;
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " AND order_sn LIKE '%" . $keyword . "%')";
    }
    if (!empty($_GPC["addtime"])) {
        $starttime = strtotime($_GPC["addtime"]["start"]);
        $endtime = strtotime($_GPC["addtime"]["end"]);
    } else {
        $starttime = strtotime("-15 day");
        $endtime = TIMESTAMP;
    }
    $condition .= " AND addtime > :start AND addtime < :end";
    $params[":start"] = $starttime;
    $params[":end"] = $endtime;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_carry_order") . $condition, $params);
    $condition .= " order by id desc";
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $export = intval($_GPC["export"]);
    if ($export != 1) {
        $condition .= " limit " . ($pindex - 1) * $psize . "," . $psize;
    }
    // var_dump($condition);exit;
    // var_dump($params);exit;
    $orders = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_carry_order") . $condition, $params);
    // var_dump($orders);exit;
    if (!empty($orders)) {
        foreach ($orders as &$da) {
            $da["pay_type_class"] = "";
            if ($da["is_pay"] == 1) {
                $da["pay_type_class"] = "have-pay";
            }
            if ($da["status"] == "4") {
                $da["cancel_reason"] = carry_order_cancel_reason($da["id"]);
            }
        }
    }
    $pager = pagination($total, $pindex, $psize);
    $pay_types = order_pay_types();
    $order_status = carry_order_status();
    $order_channels = order_channel();
    $deliveryers = deliveryer_all();
    if ($export == 1) { } else {
        include itemplate("carry/orderList");
    }
}
if($op == 'detail'){
    $id = intval($_GPC['id']);
    $order = carry_order_fetch($id);
    if (empty($order)) {
        imessage("订单不存在或已经删除", referer(), "error");
    }
    $pay_types = order_pay_types();
    $order_types = carry_order_types();
    $order_status = carry_order_status();
    $logs = carry_order_fetch_status_log($id);
    $refund_logs = carry_order_fetch_refund_status_log($id);
    include itemplate('orderDetail');
}
