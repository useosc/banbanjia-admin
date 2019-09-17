<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
mload()->lmodel('order');
if ($ta == 'list') {
    $_W['page']['title'] = '订单列表';
    $condition = " where uniacid = :uniacid and is_pay = 1 and status !=4";
    $params = array(":uniacid" => $_W['uniacid']);
    $status = isset($_GPC["status"]) ? intval($_GPC["status"]) : 1;
    $condition .= " and carry_status = :status";
    $params[":status"] = $status;
    $can_collect_order = 1;
    if ($config_carry['dispatch_mode'] != 1) {
        $can_collect_order = 0;
    }
    if ($status == 1) {
        $condition .= " and " . $_W['deliveryer']['work_status'] . " and " . $can_collect_order;
    } else {
        if ($status == 4) {
            $condition .= " and deliveryer_id = :deliveryer_id";
        } else { }
        $params[":deliveryer_id"] = $_deliveryer['id'];
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = intval($_GPC['psize']) ? intval($_GPC['psize']) : 15;
    if (!isset($order_by)) {
        $order_by = " ORDER BY id DESC";
    }
    $orders = pdo_fetchall("SELECT * FROM " . tablename('hello_banbanjia_carry_order') . $condition . $order_by . " limit " . ($pindex - 1) * $psize . ","  . $psize, $params);
    if (!empty($orders)) {
        foreach ($order as $key => &$row) { }
    }
    $result = array(
        "orders" => $orders,
    );
    imessage(error(0, $result), '', 'ajax');
}
if ($ta == 'collect') {
    $id = intval($_GPC['id']);
    $order = carry_order_fetch($id);
    if (empty($order)) {
        imessage(error(-1, '订单不存在或已删除'), '', 'ajax');
    }
    $status = carry_order_status_update($id, 'carry_assign', array("deliveryer_id" => $_deliveryer['id']));
    if (is_error($status)) {
        imessage(error(-1, $status["message"]), "", "ajax");
    }
    imessage(error(0, "抢单成功"), referer(), "ajax");
}
