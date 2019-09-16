<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";

if($ta == 'list'){
    $condition = " where uniacid = :uniacid and is_pay = 1 and status !=4";
    $params = array(":uniacid" => $_W['uniacid']);
    $status = isset($_GPC["status"]) ? intval($_GPC["status"]) : 1;
    $can_collect_order = 1;
    $pindex = max(1,intval($_GPC['page']));
    $psize = intval($_GPC['psize']) ? intval($_GPC['psize']) : 15;
    if (!isset($order_by)) {
        $order_by = " ORDER BY id DESC";
    }
    $orders = pdo_fetchall("SELECT * FROM " . tablename('hello_banbanjia_carry_order') . $condition . $order_by . " limit " . ($pindex - 1) * $psize . ","  . $psize,$params);
    if(!empty($orders)){

    }
    $result = array(
        "orders" => $orders,
    );
    imessage(error(0,$result),'','ajax');
}