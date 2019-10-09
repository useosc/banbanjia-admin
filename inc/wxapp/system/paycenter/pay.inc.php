<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "统一收银台";
if (empty($_GPC["pay_type"]) || $_GPC["pay_type"] != "alipay") {
    icheckauth();
}
$_config = $_W['we7_hello_banbanjia']['config'];
$id = intval($_GPC['id']); //order_id
$type = trim($_GPC['order_type']); //订单类型
if (empty($id) || empty($type)) {
    imessage(error(-1, "参数错误"), "", "ajax");
}
//订单路由表
$tables_router = array(
    'carry' => array("table" => "hello_banbanjia_carry_order", "cancel_status" => 4, "order_sn" => "order_sn"),
    'move' => array('table' => 'hello_banbanjia_move_order', 'cancel_status' => 4, 'order_sn' => 'order_sn'),

);
$router = $tables_router[$type];
$order = pdo_get($router["table"], array("uniacid" => $_W["uniacid"], "id" => $id));
if (empty($order)) {
    imessage(error(-1, "订单不存在或已删除"), "", "ajax");
}
if (!empty($order["is_pay"])) {
    imessage(error(-1, "该订单已付款"), "", "ajax");
}
if (isset($router["cancel_status"]) && $order["status"] == $router["cancel_status"]) {
    imessage(error(-1, "订单已取消，不能发起支付"), "", "ajax");
}
if (isset($_GPC["pay_type"]) && $_GPC["pay_type"] == "alipay") {
    $_W["member"] = get_member($order["uid"]);
}

$order_sn = $order["ordersn"] ? $order["ordersn"] : $order["order_sn"];
$record = pdo_get("hello_banbanjia_paylog", array("uniacid" => $_W["uniacid"], "order_id" => $id, "order_type" => $type, "order_sn" => $order_sn));
if (empty($record)) {
    $record = array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"], "order_sn" => $order_sn, "order_id" => $id, "order_type" => $type, "fee" => $order["final_fee"], "status" => 0, "addtime" => TIMESTAMP);
    pdo_insert("hello_banbanjia_paylog", $record);
    $record["id"] = pdo_insertid();
} else {
    if ($record["status"] == 1) {
        imessage(error(-1, "该订单已支付,请勿重复支付"), "", "ajax");
    }
    if ($order["final_fee"] != $record["fee"]) {
        pdo_update("hello_banbanjia_paylog", array("fee" => $order["final_fee"]), array("id" => $record["id"]));
        $record["fee"] = $order["final_fee"];
    }
}

//生成支付预览页信息
$logo = $_config["mall"]["logo"];

// if($type == 'move'){ //搬家订单，商家信息

// }

$routers = array(
    "carry" => array("title" => "搬运-" . $record['order_sn']),
);

$router = $routers[$type];
$title = $router['title'];
$data = array('title' => $title, 'logo' => tomedia($logo), 'fee' => $record['fee']);
//更新订单支付详情
pdo_update("hello_banbanjia_paylog", array("data" => iserializer($data)), array("id" => $record["id"]));
$params = array("module" => "hello_banbanjia", "ordersn" => $record["order_sn"], "tid" => $record["order_sn"], "user" => $_W["member"]["openid_wxapp"], "fee" => $record["fee"], "title" => $title, "order_type" => $type, "sid" => $order["sid"], "title" => urldecode($title));
//更新联合支付历史
$log = pdo_get("core_paylog", array("uniacid" => $_W["uniacid"], "module" => $params["module"], "tid" => $params["tid"]));
if (empty($log)) {
    $log = array("uniacid" => $_W["uniacid"], "acid" => $_W["acid"], "openid" => $params["user"], "module" => $params["module"], "uniontid" => date("YmdHis") . random(14, 1), "tid" => $params["tid"], "fee" => $params["fee"], "card_fee" => $params["fee"], "status" => "0", "is_usecard" => "0");
    pdo_insert("core_paylog", $log);
} else {
    if ($log["status"] == 1) {
        imessage(error(-1, "该订单已支付,请勿重复支付"), "", "ajax");
    }
    if ($log["card_fee"] != $params["fee"]) {
        pdo_update("core_paylog", array("fee" => $params["fee"], "card_fee" => $params["fee"]), array("plid" => $log["plid"]));
        $log["fee"] = $params["fee"];
        $log["card_fee"] = $log["fee"];
    }
}
$params["uniontid"] = $log["uniontid"];
$payment = get_available_payment($type, $order["sid"], true, $order["order_type"]);
if (empty($payment)) {
    imessage(error(-1, "没有有效的支付方式, 请联系网站管理员"), "", "ajax");
}


if ($_GPC['type']) { //支付预览页信息（支付时限）
    if ($type == 'carry') {
        $config_carry = get_system_config('carry');
        if (is_array($config_carry) && 0 < $config_carry['pay_time_limit']) {
            $data['pay_endtime'] = $order['addtime'] + $config_carry['pay_time_limit'] * 60;
            $data['pay_endtime_cn'] = date("Y/m/d H:i:s", $data['pay_endtime']);
            if ($data['pay_endtime'] < TIMESTAMP) {
                $datap['pay_endtime'] = 0;
            }
        }
    }

    $result = array("order" => $data, "payment" => $payment, "member" => $_W["member"]);
    $config_payment = get_system_config("payment");
    imessage(error(0, $result), "", "ajax");
}

//实际支付
$pay_type = !empty($_GPC["pay_type"]) ? trim($_GPC["pay_type"]) : $order["pay_type"];
if ($pay_type && !$_GPC["type"] && in_array($pay_type, array_keys($payment))) {
    if ($order["final_fee"] == 0) {
        $pay_type = "credit";
    }
    pdo_update("core_paylog", array("type" => $pay_type), array("uniacid" => $_W["uniacid"], "module" => $params["module"], "plid" => $log["plid"]));

    if ($pay_type == 'alipay') { //支付宝支付
        mload()->lmodel('payment');
        $alipay = $_W['we7_hello_banbanjia']['config']['payment']['alipay'];
        $ret = alipay_build($params, $alipay);
        if (is_error($ret)) {
            imessage(error(-1, $ret), "", "ajax");
        }
        imessage(error(0, $ret), "", "ajax");
        return 1;
    }

    if ($pay_type == 'credit') {
        if ($_W["member"]["credit2"] < $params["fee"]) {
            imessage(error(-1000, "余额不足以支付, 需要 " . $params["fee"] . ", 当前 " . $_W["member"]["credit2"] . " 元"), "", "ajax");
        }
        $fee = floatval($params["fee"]);
        $result = member_credit_update($_W["member"]["uid"], "credit2", 0 - $fee, array($_W["member"]["uid"], "消费余额:" . $fee . "元"));
        if (is_error($result)) {
            imessage($result["message"], "", "error");
        }
        if (!empty($_W["openid"])) {
            mc_notice_credit2($_W["openid"], $_W["member"]["uid"], $fee, 0, "线上消费");
        }
        pdo_update("core_paylog", array("status" => "1", "type" => "credit"), array("plid" => $log["plid"]));
        $site = WeUtility::createModuleSite($log["module"]);
        if (!is_error($site)) {
            $site->weid = $_W["weid"];
            $site->uniacid = $_W["uniacid"];
            $site->inMobile = true;
            $method = "payResult";
            if (method_exists($site, $method)) {
                $ret = array();
                $ret["result"] = "success";
                $ret["type"] = "credit";
                $ret["channel"] = $_W["ochannel"];
                $ret["from"] = "notify";
                $ret["tid"] = $log["tid"];
                $ret["uniontid"] = $log["uniontid"];
                $ret["user"] = $log["openid"];
                $ret["fee"] = $log["fee"];
                $ret["weid"] = $log["weid"];
                $ret["uniacid"] = $log["uniacid"];
                $ret["acid"] = $log["acid"];
                $ret["is_usecard"] = $log["is_usecard"];
                $ret["card_type"] = $log["card_type"];
                $ret["card_fee"] = $log["card_fee"];
                $ret["card_id"] = $log["card_id"];
                $result = array("data" => array("errno" => 0, "message" => "支付成功", "pay_type" => $ret["type"]));
                echo json_encode($result);
                $site->{$method}($ret);
                return 1;
            }
        }
    }
}
