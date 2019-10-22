<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
// header('location:' . iurl('offer/members/index'));
// exit;
if($op == 'index'){
    $_W['page']['title'] == '价格模板';
    
}
if($op == 'post'){
    $_W['page']['title'] == '编辑模板';

}

include itemplate('index');