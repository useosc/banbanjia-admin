<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == 'list') {
    $_W["page"]["title"] = "客服列表";
    $groups = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_service_groups") . " WHERE uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]));
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    // $condition = " WHERE a.uniacid = :uniacid";
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W['uniacid']);

    if (!empty($_GPC['groupid'])) {
        $condition .= " and group_id = :groupid";
        $params[":groupid"] = intval($_GPC['groupid']);
    }
    $user_name = $_GPC['user_name'];
    if (!empty($user_name)) {
        $condition .= " and a.user_name like '%" . $user_name . "%'";
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_service_users") . "as a left join" . tablename("hello_banbanjia_service_groups") . " as b on a.group_id = b.id" . $condition, $params);
    $users = pdo_fetchall("SELECT *,a.id,b.name as gname,a.status FROM " . tablename("hello_banbanjia_service_users") . " as a left join " . tablename("hello_banbanjia_service_groups") . " as b on (a.group_id = b.id) left join " . tablename("users") ." as c on a.uid = c.uid ". $condition . " LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
}
if ($op == 'post') {
    $_W['page']['title'] = '编辑客服';
    $groups = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_service_groups") . " WHERE uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]));
    $id = intval($_GPC['id']);
    if (0 < $id) {
        // $user = pdo_get("hello_banbanjia_service_users",array('id' => $id));
        $user = pdo_fetch("SELECT *,a.status,b.username as name FROM " . tablename("hello_banbanjia_service_users") . "as a left join" . tablename("users") . " as b on a.uid = b.uid where a.id = :id", array(":id" => $id));
    }
    if ($_W['ispost']) {
        $_GPC["user_name"] = trim($_GPC["user_name"]) ? trim($_GPC["user_name"]) : imessage(error(-1, "客服名不能为空"), "", "ajax");
        $data = array("user_name" => $_GPC["user_name"], "group_id" => intval($_GPC["groupid"]), "user_avatar" => trim($_GPC["user_avatar"]),  "status" => intval($_GPC["status"]));
        if (!$id) {
            load()->model('user');
            $insert = array("uniacid" => $_W["uniacid"], "status" => 1,"realname" => '(系统客服)', "permits" => 'service,service.index,service.from');
            $member["username"] = trim($_GPC["name"]) ? trim($_GPC["name"]) : imessage(error(-1, "账号不能为空"), referer(), "ajax");
            if (user_check(array("username" => $member["name"]))) {
                imessage(error(-1, "非常抱歉，此用户名已经被注册，你需要更换注册名称！"), referer(), "ajax");
            }
            $member["password"] = $_GPC["password"];
            $member["starttime"] = TIMESTAMP;
            $uid = user_register($member, 'pc');
            if (is_error($uid)) {
                imessage(error(-1, "密码不合法" . $uid["message"]), referer(), "ajax");
            }
            $insert["uid"] = $uid;
            $data['uid'] = $uid;
            pdo_insert("hello_banbanjia_permit_user", $insert);
            pdo_insert("uni_account_users", array("uid" => $insert["uid"], "uniacid" => $insert["uniacid"], "role" => "operator"));
            pdo_insert("hello_banbanjia_service_users", $data);
        } else {
            $username = safe_gpc_string($_GPC['name']);
            $result = pdo_update('users', array('username' => $username), array('uid' => $uid));
            $password = safe_gpc_string($_GPC['password']);
            if (!empty($password)) {
                $newpwd = user_password($password, $uid);
                pdo_update('users', array('password' => $newpwd), array('uid' => $uid));
            }

            pdo_update("hello_banbanjia_service_users", $data, array("id" => $id));
            $uid = pdo_get('hello_banbanjia_service_users', array('id' => $id), array('uid'));
        }
        imessage(error(0, "编辑客服成功"), iurl("service/user/list"), "ajax");
    }
}
if ($op == "del") {
    $id = intval($_GPC["id"]);
    $uid = pdo_get('hello_banbanjia_service_users', array('id' => $id), array('uid'));
    pdo_delete("hello_banbanjia_service_users", array("id" => $id));
    pdo_delete("hello_banbanjia_permit_user", array("uniacid" => $_W["uniacid"], "uid" => $uid));
    pdo_delete("users", array("uid" => $uid));
    imessage(error(0, "删除客服成功"), "", "ajax");
}
include itemplate('service/user');
