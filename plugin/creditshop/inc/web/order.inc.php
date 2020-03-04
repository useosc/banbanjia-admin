<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if($op == 'list') {
    $_W["page"]["title"] = "积分商城兑换记录";

    include itemplate('order');
}