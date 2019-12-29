<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == 'list') {
    $_W['page']['title'] = '菜单列表';
    $condition = " where uniacid = :uniacid and `version` = 1";
    $params = array(":uniacid" => $_W['uniacid']);
    $keyword = trim($_GPC['keyword']);
    if (!empty($keyword)) {
        $condition .= " and name like '%" . $keyword . "%'";
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_diypage_menu") . $condition, $params);
    $menus = pdo_fetchall("select * from " . tablename("hello_banbanjia_diypage_menu") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total,$pindex,$psize);
}
if($op == 'post'){
    
}
include itemplate('menu');
