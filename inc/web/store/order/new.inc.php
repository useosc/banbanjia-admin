<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "新建订单";
mload()->lmodel('bpm');
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if($ta == 'index'){
    include itemplate('store/order/new');
}