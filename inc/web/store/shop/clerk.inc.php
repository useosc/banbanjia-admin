<?php
defined("IN_IA") or exit("Access Denied");
mload()->lmodel("clerk");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if ($ta == "list") {
    $_W["page"]["title"] = "员工列表";
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store_clerk") . "WHERE uniacid = :uniacid and sid = :sid", array(":uniacid" => $_W["uniacid"], ":sid" => $sid));
    $data = pdo_fetchall("SELECT *, a.id as aid, a.role as role FROM " . tablename("hello_banbanjia_store_clerk") . "as a left join" . tablename("hello_banbanjia_clerk") . "as b on a.clerk_id = b.id WHERE a.uniacid = :uniacid and a.sid = :sid ORDER BY aid DESC LIMIT " . ($pindex - 1) * $psize . ", " . $psize, array(":uniacid" => $_W["uniacid"], ":sid" => $sid));
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["extra"] = iunserializer($value["extra"]);
        }
    }
    $pager = pagination($total, $pindex, $psize);
}
if ($ta == 'post') {
    $_W["page"]["title"] = "编辑员工";
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $clerk = pdo_get('hello_banbanjia_clerk_permit', array('uniacid' => $_W['uniacid'], 'id' => $id));
        
    }

    if ($_W['ispost']) {
        mload()->lmodel('clerk');
        $clerk = array();
        $insert = array('uniacid' => $_W['uniacid'], 'roleid' => intval($_GPC['roleid']), 'status' => intval($_GPC['status']), 'realname' => trim($_GPC['realname']), 'mobile' => trim($_GPC['mobile']), 'permits' => implode(',', $_GPC['permits']));
        if (0 < $insert["roleid"]) {
            $insert["permits"] = implode(",", array_diff($_GPC["permits"], $roles[$insert["roleid"]]["permits"]));
        }
    }
}
include itemplate("store/shop/clerk");
