<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('member');
mload()->lmodel('store');
if ($_W["we7_hello_banbanjia"]["global"]["development"] == 1) {
    ini_set("display_errors", "1");
    error_reporting(30719 ^ 8);
}
$_W["role"] = "consumer";
$_W["role_cn"] = "下单顾客";