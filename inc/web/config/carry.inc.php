<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "range";

if($op == 'order'){
    $_W['page']['title'] = "订单相关";
    $order = $_config["carry"]["order"];
    if (!empty($order["deliveryer_transfer_reason"])) {
        $order["deliveryer_transfer_reason"] = implode("\n", $order["deliveryer_transfer_reason"]);
    }
    if (!empty($order["deliveryer_cancel_reason"])) {
        $order["deliveryer_cancel_reason"] = implode("\n", $order["deliveryer_cancel_reason"]);
    }
    include itemplate("config/carry-order");
}