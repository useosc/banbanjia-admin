<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
$_W["page"]["title"] = "客户管理";
if ($ta == "list") { 
    $condition = " where uniacid = :uniacid AND sid = :sid";
    $params[":uniacid"] = $_W["uniacid"];
    $params[":sid"] = $sid;
    $lists = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store_customers") . $condition ,$params);
    imessage(error(0,$lists),'','ajax');
}
if ($ta == 'add') {
    // error_reporting(E_ALL);
    $id = intval($_GPC['id']);
    if (0 < $id) {
        $customers = store_fetch_customers($id);
    }

    if ($_W['ispost']) {
        $data = array(
            "uniacid" => $_W['uniacid'], "sid" => $sid,
            "name" => trim($_GPC['name']), "mobile" => trim($_GPC['mobile']),
            "addtime" => TIMESTAMP, "create_clerk_id" => $_W['manager']['id'],
            "owner_clerk_id" => $_W['manager']['id'], "customer_no" => TIMESTAMP
        );
        pdo_insert("hello_banbanjia_store_customers",$data);
        $cusid = pdo_insertid();

        imessage(error(0, "编辑成功"), '', 'ajax');
    }

    $result = array("customers" => $customers);
    imessage(error(0, $result), '', 'ajax');
    return 1;
}
