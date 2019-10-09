<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if (empty($_W['deliveryer']['is_carry'])) {
    imessage(error(-1, '您没有接搬运单的权限，请联系管理员授权'), "", "ajax");
}
mload()->lmodel('order');
if ($ta == 'list') {
    $_W['page']['title'] = '订单列表';
    $condition = " where uniacid = :uniacid and is_pay = 1 and status !=4";
    $params = array(":uniacid" => $_W['uniacid']);
    $status = isset($_GPC["status"]) ? intval($_GPC["status"]) : 1;
    $condition .= " and carry_status = :status";
    $params[":status"] = $status;
    $can_collect_order = 1;
    if ($config_carry['dispatch_mode'] != 1 && !$config_delivery["can_collect_order"]) {
        $can_collect_order = 0;
    }
    if ($status == 1) {
        $condition .= " and " . $_W['deliveryer']['work_status'] . " and " . $can_collect_order;
    } else {
        if ($status == 4) {
            $condition .= " and deliveryer_id = :deliveryer_id";
        } else { //转单
            $condition .= " and deliveryer_id = :deliveryer_id";
         }
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
        "can_collect_order" => $can_collect_order,
        "deliveryer" => $_W['deliveryer']
    );
    imessage(error(0, $result), '', 'ajax');
}
if ($ta == 'collect') { //旧接单
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
if ($ta == 'status') { //新接单
    $id = intval($_GPC['id']);
    $type = trim($_GPC['type']);

    if (empty($id)) {
        imessage(error(-1, '请选择订单'), '', 'ajax');
    }
    $types = array('carry_assign', 'carry_indoor', 'carry_success', 'direct_transfer_reply', 'carry_transfer', 'cancel', 'direct_transfer');
    if (!in_array($type, $types)) {
        imessage(error(-1, '订单操作有误'), '', 'ajax');
    }
    $extra = array('deliveryer_id' => $deliveryer['id'], 'carry_handle_type' => $_GPC['from']);

    $result = carry_order_status_update($id, $type, $extra);
    if (is_error($result)) {
        imessage($result, '', 'ajax');
    }

    imessage(error(0,$result['message']),'','ajax');
}
