<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if($op == 'list'){
    $_W['page']['title'] = '企业列表';
    include itemplate('merchant/list');
}
if($op == 'post'){
    $_W['page']['title'] = '添加企业';
    
    include itemplate('merchant/post');
}