<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list') {
    error_reporting(E_ERROR);
    $condition = " uniacid = :uniacid and sid = :sid";
    $params[":uniacid"] = $_W['uniacid'];
    $params[':sid'] = $sid;
    $keyword = trim($_GPC['keyword']);
    if (!empty($_GPC['keyword'])) {
        $condition .= " and (name like '%" . $keyword . "%' or card = '" . $keyword . "or holders like '%" . $keyword . "%')";
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store_bank_account") . " WHERE " . $condition . " ORDER BY displayorder DESC,id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    if(!empty($data)) {
        foreach($data as &$item) {
            $item['addtime_cn'] = date("Y-m-d",$item['addtime']);
        }
    }
    imessage(error(0,$data),"",'ajax');
}

if($ta == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $bankAccount = pdo_get("hello_banbanjia_store_bank_account", array("uniacid" => $_W['uniacid'], 'id' => $id));
    }
    if ($_W['ispost']) {
        if(!empty($_GPC['displayorder'])) {
            $displayorder = intval($_GPC['displayorder']);
        }else{
            $displayorder =  100;
        }
        $insert = array(
            "uniacid" => $_W['uniacid'], 'sid' => $_W['sid'],
            "name" => trim($_GPC['name']), 'card' => trim($_GPC['card']), 'address' => trim($_GPC['card']),
            'holders' => trim($_GPC['holders']), 
            'displayorder' => $displayorder, 'type' => intval($_GPC['type']),
            'status' => intval($_GPC['status']),'addtime' => TIMESTAMP
        );
        pdo_insert("hello_banbanjia_store_bank_account",$insert);
        $aid = pdo_insertid();
        imessage(error(0,'添加成功'),'','ajax');
    }
}