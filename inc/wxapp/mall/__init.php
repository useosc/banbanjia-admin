<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('common');
mload()->lmodel('member');
mload()->lmodel('store');
$_W['we7_hello_banbanjia']['global'] = get_global_config();
if ($_W["we7_hello_banbanjia"]["global"]["development"] == 1) {
    ini_set("display_errors", "1");
    error_reporting(30719 ^ 8);
}
$_W['we7_hello_banbanjia']['config'] = get_system_config();
$_config_mall = $_W['we7_hello_banbanjia']['config']['mall'];

$config_diypage = get_plugin_config('diypage');
$_W["we7_wxapp"]["config"]["diy"] = $config_diypage["diy"];
$config_wxapp = $_config_wxapp = $_W["we7_wxapp"]["config"];

$_W["role"] = "consumer";
$_W["role_cn"] = "下单顾客";