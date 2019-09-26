<?php
defined("IN_IA") or exit("Access Denied");
function alipay_build($params, $alipay = array())
{
    global $_W;
    $config_paycallback = $_W["we7_hello_banbanjia"]["config"]["paycallback"];
    $notify_use_http = intval($config_paycallback["notify_use_http"]);
    load()->func("communication");
    $trade_type = $alipay["trade_type"];
    $set = array();
 }
