<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('deliveryer');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "订单列表";
    //订单统计
    $condition = " where uniacid = :uniacid and status = 3";
    $filter_type = trim($_GPC["filter_type"]) ? trim($_GPC["filter_type"]) : "process";
    $status = intval($_GPC['status']);
    if(0 < $status){
        $condition .= " AND status = :status";
        $params[":status"] = $status;
    }else{

    }

    $is_pay = isset($_GPC['is_pay']) ? intval($_GPC['is_pay']) : 1;
    
    include itemplate("domestice/orderList");
}