<?php
defined("IN_IA") or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
$user = pdo_get("hello_banbanjia_service_users", array('uid' => $_W['uid']));
if ($op == 'index') {
    $_W['page']['title'] = '访客列表';
    // echo "<pre>";print_r($_W);echo "<pre>";exit;

    if ($_W['ispost']) {
        if (!$_GPC['name'] || !$_GPC['groupid']) {
            imessage(error(0, '请填写完整信息', iurl('service/addUser'), 'ajax'));
        }
        $insert = array('uid' => $_W['uid'], 'user_name' => $_GPC['name'], 'group_id' => $_GPC['groupid'], 'user_avatar' => trim($_GPC['user_avatar']), 'status' => 1);
        pdo_insert("hello_banbanjia_service_users", $insert);
        imessage(error(0, "编辑操作员成功"), iurl("service/from/index"), "ajax");
    }
    //判断是否有客服相关信息
    $service_user = pdo_get('hello_banbanjia_service_users', array('uid' => $_W['uid']), array('id'));
    if (empty($service_user)) {
        $_W['page']['title'] = '编辑信息';
        $groups = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_service_groups") . " WHERE uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]));
        return include itemplate('service/addUser');
    }

    //获取服务用户列表
    //此处只查询过去 三个小时 内的未服务完的用户
    $userList = pdo_fetchall("SELECT user_id id,user_name name,user_avatar avatar,user_ip ip FROM " . tablename("hello_banbanjia_service_log") . " WHERE kf_id = :kf_id " . " and end_time = 0", array(":kf_id" => $user['id']));
    include itemplate('service/from');
}else{
    if($op == 'chatlog'){
        $limit = 10; //一次10条记录
        $offset = ($_GPC['page'] -1) * $limit;
        $uid = $_GPC['uid'];
        $logs = pdo_fetchall("SELECT * FROM " .tablename("hello_banbanjia_service_chat_log") . " WHERE (from_id = :uid AND to_id =:kfid) OR (from_id = :kfid AND to_id =:uid) ORDER BY id DESC LIMIT " . $offset . "," . $limit,array("uid"=>$uid,"kfid"=>'KF'.$user[id]));
        foreach($logs as $key => $vo){
            $logs[$key]['time_line'] = date('Y-m-d H:i:s',$vo['time_line']);
        }
        imessage(error(0,$logs,'','ajax'));
    }
}
