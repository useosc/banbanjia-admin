<?php
ini_set("display_errors", "1"); //显示出错信息
error_reporting(E_ALL ^ E_NOTICE);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';

if ($op == 'index') {
    $_W['page']['title'] = '客服概况';
    $data = pdo_get('hello_banbanjia_service_now_data',array('id'=>1));
    include itemplate('service/index');
}
