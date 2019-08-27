<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('cron');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "task";
set_time_limit(0);
if ($op == "task") {
    cron_order();
    exit("success");
}
if ($op == "order_notice") {
    if ($_GPC["_ac"] == "carry" && $_GPC["_status_order_notice"]) {
        $order = pdo_get("hello_banbanjia_order", array("uniacid" => $_W["uniacid"], "status" => 1, "is_pay" => 1));
        if (!empty($order)) {
            exit("success");
        }
        exit("error");
    }
}
