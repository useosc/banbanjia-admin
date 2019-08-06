<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
$all_permits = get_all_permits();
if($op == 'list'){
    $_W['page']['title'] = '角色管理';
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid"=>$_W['uniacid']);
    $status = isset($_GPC['status']) ? intval($_GPC['status']) : -1;
    if(-1 < $status){
        $condition .= " and status = :status";
        $params['status'] = $status;
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_permit_role") . $condition, $params);
    $roles = pdo_fetchall("select * from " . tablename("hello_banbanjia_permit_role") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    $user_nums = pdo_fetchall("select count(*) as total, roleid from " . tablename("hello_banbanjia_permit_user") . " where uniacid = :uniacid group by roleid", array(":uniacid" => $_W["uniacid"]), "roleid");
}
if($op == 'post'){
    $_W['page']['title'] = '编辑角色';
    $id = intval($_GPC['id']);
    if($_W['ispost']){
        $insert = array('uniacid' => $_W['uniacid'],'rolename' => trim($_GPC['rolename']),'status'=>intval($_GPC['status']),'permits'=>implode(',',$_GPC['permits']));
        if( 0 < $id){
            pdo_update('hello_banbanjia_permit_role',$insert,array('uniacid'=>$_W['uniacid'],'id'=>$id));
        }else{
            pdo_insert('hello_banbanjia_permit_role',$insert);
        }
        imessage(error(0,'编辑角色成功'),iurl('permit/role/list'),'ajax');
    }
    if (0 < $id) {
        $role = pdo_get("hello_banbanjia_permit_role", array("uniacid" => $_W["uniacid"], "id" => $id));
        $role["permits"] = explode(",", $role["permits"]);
    }
}

include itemplate('permit/role');