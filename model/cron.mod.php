<?php

defined("IN_IA") or exit("Access Denied");
function cron_order()
{
    global $_W;
    load()->func("communication");
    $_W["role"] = "system";
    $_W["role_cn"] = "系统";
    $key = "we7_hello_banbanjia:" . $_W["uniacid"] . ":task:lock:60";
    if (!check_cache_status($key, 60)) {
        $config_carry = $_W['we7_banbanjia']['config']['carry']['order'];
        
    }
}