<?php
error_reporting(E_ALL ^ E_NOTICE);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('store');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
if($op == 'index'){
    $_W['page']['title'] = '评价列表';
    $condition = " where c.uniacid = :uniacid and s.id = c.sid and m.uid = c.uid";
    $params[":uniacid"] = $_W['uniacid'];
    $pindex = max(1,intval($_GPC['page']));
    $psize = 20;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store_comment") . " where uniacid =:uniacid",$params);
    $comments = pdo_fetchall("SELECT c.*,m.nickname as memberName,s.title as storeTitle FROM " . tablename("hello_banbanjia_store_comment") . " as c," . tablename("hello_banbanjia_store") . " as s," . tablename("hello_banbanjia_members") ." as m " . $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize,$params);
    $pager = pagination($total,$pindex,$psize);
}
if($op == 'status') {
    $id = intval($_GPC['id']);
    $data = array("status" => intval($_GPC['status']));
    pdo_update("hello_banbanjia_store_comment",$data,array("uniacid" => $_W['uniacid'],'id' => $id));
    imessage(error(0, ""), "", "ajax");
}
include itemplate("merchant/comment");