<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "订单管理";
mload()->lmodel('bpm');
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if ($ta == 'list') {
    // $users = getBpmUsers();
    $cases = getBpmCases();
    // var_dump($cases);exit;

    include itemplate("store/order/list");
}
