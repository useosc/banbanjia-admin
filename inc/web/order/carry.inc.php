<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('deliveryer');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "订单管理";
    //订单统计
    $filter_type = trim($_GPC["filter_type"]) ? trim($_GPC["filter_type"]) : "process";
    include itemplate("order/carryList");
}