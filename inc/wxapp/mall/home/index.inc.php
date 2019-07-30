<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
$config_mall = $_W['w7_hello_banbanjia']['config']['mall'];
if(defined("IN_WXAPP")){
    icheckauth();
}

if($ta == 'index'){
    $config_app_customer = $_W['we7_hello_banbanjia']['config']['app']['customer'];
    mload()->lmodel('plugin');
    mload()->lmodel('diy');
}