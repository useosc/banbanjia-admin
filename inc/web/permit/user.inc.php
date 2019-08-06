<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'role';
$all_permits = get_all_permits();
if ($op == 'list') {
    $_W['page']['title'] = '操作员管理';
    $condition = " where a.uniacid = :uniacid";
    $params = array(":uniacid" => $_W['uniacid']);
    $status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
    if (-1 < $status) {
        $condition .= " and a.status = :status";
        $params["status"] = $status;
    }
    $pindex = max(1,intval($_GPC['page']));
    $psize = 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_permit_uer") . " as a left join " . tablename("users") . " as b on a.uid = b.uid" . $condition, $params);
    $users = pdo_fetchall("select a.*, b.username from " . tablename("hello_banbanjia_permit_user") . " as a left join " . tablename("users") . " as b on a.uid = b.uid" . $condition . " order by a.id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    $roles = pdo_fetchall("select id, rolename from " . tablename("hello_banbanjia_permit_role") . " where uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]), "id");
}

include itemplate('permit/user');
