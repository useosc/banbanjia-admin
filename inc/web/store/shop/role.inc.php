<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'list';
$all_permits = get_all_store_permits();
if($ta == 'list'){
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
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_clerk_role") . $condition, $params);
    $roles = pdo_fetchall("select * from " . tablename("hello_banbanjia_clerk_role") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    $user_nums = pdo_fetchall("select count(*) as total, roleid from " . tablename("hello_banbanjia_clerk_permit") . " where uniacid = :uniacid group by roleid", array(":uniacid" => $_W["uniacid"]), "roleid");
}
if($ta == 'post'){
    $_W['page']['title'] = '编辑角色';
    $id = intval($_GPC['id']);
    if($_W['ispost']){
        $insert = array('uniacid' => $_W['uniacid'],'rolename' => trim($_GPC['rolename']),'status'=>intval($_GPC['status']),'permits'=>implode(',',$_GPC['permits']));
        if( 0 < $id){
            pdo_update('hello_banbanjia_clerk_role',$insert,array('uniacid'=>$_W['uniacid'],'id'=>$id));
        }else{
            pdo_insert('hello_banbanjia_clerk_role',$insert);
        }
        imessage(error(0,'编辑角色成功'),iurl('store/shop/role/list'),'ajax');
    }
    if (0 < $id) {
        $role = pdo_get("hello_banbanjia_clerk_role", array("uniacid" => $_W["uniacid"], "id" => $id));
        $role["permits"] = explode(",", $role["permits"]);
    }
}
if ($ta == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_clerk_role", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}
if ($ta == "del") {
    $ids = $_GPC["id"];
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_delete("hello_banbanjia_clerk_role", array("uniacid" => $_W["uniacid"], "id" => $id));
        pdo_update("hello_banbanjia_clerk_permit", array("roleid" => 0), array("uniacid" => $_W["uniacid"], "roleid" => $id));
    }
    imessage(error(0, "删除角色成功"), "", "ajax");
}
include itemplate('store/shop/role');