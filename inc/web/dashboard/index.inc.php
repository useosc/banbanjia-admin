<?php
defined('IN_IA') or exit('Access Denied');
global $_W, $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';

if($op == 'index') {
    $_W['page']['title'] = '运营概括';
    $stat = array();

    $deliveryerCondition = ' where uniacid = :uniacid';
	$deliveryerParams = array(
		':uniacid' => $_W['uniacid']
	);
	$deliveryer['total_deliveryer'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_deliveryer') . $deliveryerCondition, $deliveryerParams));
	$deliveryer['total_work_deliveryer'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_deliveryer') . "{$deliveryerCondition} and status = 1 and work_status = 1", $deliveryerParams));
	$deliveryer['total_rest_deliveryer'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_deliveryer') . "{$deliveryerCondition} and status = 1 and work_status = 0", $deliveryerParams));
	$deliveryer['total_storage_deliveryer'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_deliveryer') . "{$deliveryerCondition} and status = 2", $deliveryerParams));
}
include itemplate('dashboard/index');