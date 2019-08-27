<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('member');

$_W["role"] = "consumer";
$_W["role_cn"] = "下单顾客";