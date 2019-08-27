<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";

if($ta == 'list'){
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W['uniacid']);
    $status = isset($_GPC["status"]) ? intval($_GPC["status"]) : 3;
    $can_collect_order = 1;



    $pindex = max(1,intval($_GPC['page']));
    $psize = intval($_GPC['psize']);
    if (!isset($order_by)) {
        $order_by = " ORDER BY id DESC";
    }
    
    $orders = pdo_fetchall("SELECT * FROM " . tablename('hello_banbanjia_domestic_order') . $condition . $order_by,$params);
    imessage(error(0,$orders),'','ajax');
}