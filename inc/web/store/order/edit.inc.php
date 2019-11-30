<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "订单管理";
mload()->lmodel('bpm');

$app_uid = $_GPC['app_uid'];
$del_index = $_GPC['del_index'];
$action = $_GPC['action'];

$javascript = getBpmCaseContent(array('app_uid' => $app_uid, 'del_index' => $del_index, 'action' => $action));

if (is_error($javascript)) {
    imessage("获取订单信息出错", referer(), "error");
}

$javascriptContent = $javascript['content'];

include itemplate('store/order/edit');
