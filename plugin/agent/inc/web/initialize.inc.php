<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
if($op == 'index') {
    $_W["page"]["title"] = "数据初始化";
    set_time_limit(0);
    
}
include itemplate('initialize');