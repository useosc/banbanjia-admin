<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "订单管理";
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list'){
    
    include itemplate("store/order/list");
}