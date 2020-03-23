<?php

use GPBMetadata\Google\Protobuf\Timestamp;

defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth();
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
if ($op == "index") {
    $result = array("adv" => creditshop_adv_get(1), "goods" => creditshop_goodsall_get(), "category" => creditshop_category_get(1), "member" => $_W["member"]);
    imessage(error(0, $result), "", "ajax");
}
if ($op == "goods") {
    $goods = creditshop_goodsall_get();
    $result = array("goods" => $goods);
    imessage(error(0, $result), "", "ajax");
}
if ($op == "detail") {
    $id = $_GPC["id"];
    $good = creditshop_goods_get($id);
    $can = creditshop_can_exchange_goods($id);
    if ($can["errno"] == -2) {
        $good["can"] = 0;
    } else {
        $good["can"] = 1;
    }
    $goods = creditshop_goodsall_get(array("page" => 1, "psize" => 4, "type" => $good["type"]));
    $records = creditshop_record_get();
    $goods_keys = array();
    if (!empty($goods)) {
        foreach ($goods as $key => $value) {
            if ($value["id"] == $id) {
                unset($goods[$key]);
            }
        }
    }
    $goods = array_slice($goods, 0, 3, true);
    $member = $_W["member"];
    $member["credit1"] = intval($member["credit1"]);
    $result = array("good" => $good, "goods" => $goods, "member" => $member, "records" => $records);
    imessage(error(0, $result), "", "ajax");
}

if ($op == 'sign') { //用户签到页面
    $result = array('member' => $_W['member'], 'is_sign' => false, 'addcredit1' => 10);
    $today = date('Ymd');
    $last_sign_day = date('Ymd', $_W['member']['last_sign_time']);
    $continue_sign_day = $_W['member']['continue_sign_day'];
    $early_sign_day = date("Ymd", strtotime("-1 day"));
    $signset = get_system_config('member.sign');
    $addcredit1 = $signset[$continue_sign_day];
    if ($continue_sign_day >= 7) {
        $addcredit1 = $signset[0];
        $result['addcredit1'] = $addcredit1;
    }
    if ($today == $last_sign_day) {
        $result['is_sign'] = true;
    }
    imessage(error(0, $result), '', 'ajax');
}
if ($op == 'postsign') {
    $today = date('Ymd');
    $last_sign_day = date('Ymd', $_W['member']['last_sign_time']);
    $continue_sign_day = $_W['member']['continue_sign_day'];
    if ($today == $last_sign_day) {
        imessage(error(1, '今天已经签过了'), "", "ajax");
    }
    $early_sign_day = date("Ymd", strtotime("-1 day"));
    $signset = get_system_config('member.sign');
    $addcredit1 = $signset[$continue_sign_day];
    pdo_update("hello_banbanjia_members", array("continue_sign_day" => $continue_sign_day + 1, "sign_total_day" => $sign_total_day + 1, "last_sign_time" => TIMESTAMP), array('uid' => $_W['member']['uid']));
    if ($continue_sign_day >= 7 || ($last_sign_day != $early_sign_day)) {
        $addcredit1 = $signset[0];
        pdo_update("hello_banbanjia_members", array("continue_sign_day" => 1), array('uid' => $_W['member']['uid']));
    }
    pdo_insert("hello_banbanjia_sign_log", array(
        'uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'signtime' => TIMESTAMP,
        'addcredit1' => $addcredit1
    ));
    $status = member_credit_update($_W["member"]["uid"], "credit1", $addcredit1);
    if (is_error($status)) {
        imessage(-1, $status["message"], "", "ajax");
    }
    imessage(error(0, '签到成功'), "", "ajax");
}
