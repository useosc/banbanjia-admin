<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckAuth();
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
mload()->lmodel('order');
if ($ta == 'create') {
    // $id = intval($_GPC['id']);
    $params = json_decode(htmlspecialchars_decode($_GPC['extra']), true);
    if (empty($params)) {
        imessage(error(-1, '参数错误'), '', 'ajax');
    }
    $order = carry_order_calculate_delivery_fee($params);
    // $delivery_info = $order['deliveryInfo'];
    $data = array(
        "uniacid" => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'order_type' => $params['order_type'],
        'order_sn' => date("YmdHis") . random(6, true),
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
    pdo_insert('hello_banbanjia_carry_order', $data);
    $orderid = pdo_insertid();
    // 隐私号
    // carry_order_insert_status_log($orderid,"place_order");
    // carry_order_insert_discount($orderid,$order['activityed']['list']);
    // carry_order_insert_status_log($orderid,"place_order");
    imessage(error(0, $orderid), '', 'ajax');
}
if ($ta == 'list') {
    // $total_user = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_carry_order") . " where uniacid = :uniacid", array(":uniacid" => $_W['uniacid']));
    $condition = " where uniacid = :uniacid and uid = :uid";
    $params = array(":uniacid" => $_W['uniacid'], ":uid" => $_W["member"]["uid"]);
    $pindex = max(1, intval($_GPC['page']));
    $psize = intval($_GPC['psize']) ? intval($_GPC['psize']) : 7;
    $orders = pdo_fetchall("select * from " . tablename("hello_banbanjia_carry_order") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);

    $result = array("orders" => $orders);
    imessage(error(0,$result),"","ajax");
    return 1;
}
if($ta == 'detail'){
    $_W["page"]["title"] = "订单详情";
    $id = intval($_GPC["id"]);
    $order = carry_order_fetch($id);
    if (empty($order)) {
        imessage(error(-1, "订单不存在或已删除"), "", "ajax");
    }
    $log = pdo_fetch("select * from " . tablename("hello_banbanjia_carry_order_status_log") . " where uniacid = :uniacid and oid = :oid order by id desc", array(":uniacid" => $_W["uniacid"], ":oid" => $id));
    $logs = carry_order_fetch_status_log($id);
    if (!empty($logs)) {
        $maxid = max(array_keys($logs));
        $minid = min(array_keys($logs));
        foreach ($logs as &$log) {
            $log["addtime"] = date("H:i", $log["addtime"]);
        }
    }
    $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "id" => $order["deliveryer_id"]));
    $order_types = carry_order_types();
    $pay_types = order_pay_types();
    $order_status = carry_order_status();

    $show_location = 0;
    if ($order["status"] == 2 && in_array($order["carry_handle_type"], array("app", "wxapp"))) {
        $show_location = 1;
    }
    $result = array("order" => $order, "deliveryer" => $deliveryer, "log" => $log, "logs" => $logs, "maxid" => $maxid, "minid" => $minid, "show_location" => $show_location, "config_mall" => array("mobile" => $_config_mall["mobile"]));
    imessage(error(0,$result),'','ajax');
}
