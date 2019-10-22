<?php
defined("IN_IA") or exit("Access Denied");
mload()->lmodel("clerk");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
$all_permits = get_all_store_permits();
if ($ta == "list") {
    $_W["page"]["title"] = "员工列表";
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $condition = " where a.uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $status = isset($_GPC["status"]) ? intval($_GPC["status"]) : -1;
    if (-1 < $status) {
        $condition .= " and a.status = :status";
        $params["status"] = $status;
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and b.username like '%" . $keyword . "%'";
    }
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store_clerk") . "WHERE uniacid = :uniacid and sid = :sid", array(":uniacid" => $_W["uniacid"], ":sid" => $sid));
    $data = pdo_fetchall("SELECT *, a.id as aid, a.role as role FROM " . tablename("hello_banbanjia_store_clerk") . "as a left join" . tablename("hello_banbanjia_clerk") . "as b on a.clerk_id = b.id WHERE a.uniacid = :uniacid and a.sid = :sid ORDER BY aid DESC LIMIT " . ($pindex - 1) * $psize . ", " . $psize, array(":uniacid" => $_W["uniacid"], ":sid" => $sid));
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["extra"] = iunserializer($value["extra"]);
        }
    }
    $pager = pagination($total, $pindex, $psize);
    $roles = pdo_fetchall("select id, rolename from " . tablename("hello_banbanjia_clerk_role") . " where uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]), "id");
}
if ($ta == 'post') {
    $_W["page"]["title"] = "编辑员工";
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $clerk = pdo_get('hello_banbanjia_store_clerk', array('uniacid' => $_W['uniacid'], 'id' => $id));
        $clerk['title'] = pdo_fetchcolumn("select title,mobile from " . tablename("hello_banbanjia_clerk") . " where id = :clerk_id", array(":clerk_id" => $clerk['clerk_id']));
        $clerk['mobile'] = pdo_fetchcolumn("select mobile from " . tablename("hello_banbanjia_clerk") . " where id = :clerk_id", array(":clerk_id" => $clerk['clerk_id']));
        $clerk["permits"] = explode(",", $clerk["permits"]);
    }
    $roles = pdo_fetchall("select id,rolename,permits from " . tablename("hello_banbanjia_clerk_role") . " where uniacid = :uniacid", array(":uniacid" => $_W['uniacid']), "id");
    if (!empty($roles)) {
        foreach ($roles as &$val) {
            $val["permits"] = explode(",", $val["permits"]);
        }
    }

    if ($_W['ispost']) {
        mload()->lmodel('clerk');
        $clerk = array();
        $password = trim($_GPC["password"]) ? trim($_GPC["password"]) : imessage(error(-1, "密码不能为空"), "", "ajax");
        $insert = array('uniacid' => $_W['uniacid'], 'status' => intval($_GPC['status']), 'title' => trim($_GPC['title']), 'mobile' => trim($_GPC['mobile']), "salt" => random(6), "token" => random(32), "addtime" => TIMESTAMP);
        $insert["password"] = md5(md5($insert["salt"] . $password) . $insert["salt"]);
        if (0 < $_GPC["roleid"]) {
            $permits = implode(",", array_diff($_GPC["permits"], $roles[$_GPC["roleid"]]["permits"]));
        }
        pdo_insert("hello_banbanjia_clerk", $insert);
        $clerk_id = pdo_insertid();
        pdo_insert("hello_banbanjia_store_clerk", array("uniacid" => $_W['uniacid'], 'sid' => $sid, 'clerk_id' => $clerk_id, 'roleid' => intval($_GPC['roleid']), 'status' => 1, 'permits' => $permits));
    }
}
include itemplate("store/shop/clerk");
