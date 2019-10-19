<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";

if ($ta == 'info') {
    $store = store_fetch($sid);

    $result = array('store' => $store);
    imessage(error(0, $result), '', 'ajax');
    return 1;
}
