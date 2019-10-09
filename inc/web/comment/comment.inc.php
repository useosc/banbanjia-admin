<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
mload()->lmodel('deliveryer');
if($op == 'list'){
    $_W['page']['title'] = '用户评价';
    $pindex = max(1,intval($_GPC['page']));
    $psize = 15;
    echo 'sss';
}