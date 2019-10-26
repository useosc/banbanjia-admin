<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'list';
if ($ta == 'list') {
    $_W['page']['title'] = '合同分类';
    $condition = " where uniacid = :uniacid and sid = :sid";
    $params = array(":uniacid" => $_W['uniacid'], ":sid" => $sid);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_hetong_category") . $condition, $params);
    $cates = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_hetong_category") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    $hetong_nums = pdo_fetchall("select count(*) as total, cateid from " . tablename("hello_banbanjia_store_hetong") . " where uniacid = :uniacid and sid = :sid group by cateid", array(":uniacid" => $_W["uniacid"], ":sid" => $sid), "cateid");
    // var_dump($cates);exit;
}
if ($ta == 'post') {
    $_W['page']['title'] = '编辑分类';
    $id = intval($_GPC['id']);
    if ($_W['ispost']) {
        $insert = array('uniacid' => $_W['uniacid'], 'catename' => trim($_GPC['catename']), 'sid' => $sid);
        if (0 < $id) {
            pdo_update('hello_banbanjia_store_hetong_category', $insert, array('uniacid' => $_W['uniacid'], 'id' => $id));
        } else {
            pdo_insert('hello_banbanjia_store_hetong_category', $insert);
        }
        imessage(error(0, '编辑分类成功'), iurl('store/contract/cate/list'), 'ajax');
    }
}
if ($ta == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_store_hetong_category", array("status" => $status), array("uniacid" => $_W["uniacid"], "sid" => $sid, "id" => $id));
    imessage(error(0, ""), "", "ajax");
}
if ($ta == "del") {
    $ids = $_GPC["id"];
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_delete("hello_banbanjia_store_hetong_category", array("uniacid" => $_W["uniacid"], "id" => $id));
        pdo_update("hello_banbanjia_store_hetong", array("cateid" => 0), array("uniacid" => $_W["uniacid"], "cateid" => $id));
    }
    imessage(error(0, "删除分类成功"), "", "ajax");
}

include itemplate('store/contract/cate');
