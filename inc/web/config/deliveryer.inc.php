<?php
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "settle";
if ($op == "settle") {
    $_W["page"]["title"] = "搬运工申请";
    if ($_W["ispost"]) {
        // $settle = array("mobile_verify_status" => intval($_GPC["mobile_verify_status"]), "idCard" => intval($_GPC["idCard"]));
        set_config_text("搬运工入驻协议", "agreement_delivery", htmlspecialchars_decode($_GPC["agreement_delivery"]));
        // set_system_config("delivery.settle", $settle);
        imessage(error(0, "搬运工申请设置成功"), referer(), "ajax");
    }
    $settle = $_config["delivery"]["settle"];
    $settle["agreement_delivery"] = get_config_text("agreement_delivery");
    include itemplate("config/deliveryer-settle");
} else {
    if ($op == 'cash') {
        $_W["page"]["title"] = "提成及提现";
        $deliveryerCash = $_config["delivery"]["cash"];
        if ($_W['ispost']) {
            $form_type = trim($_GPC["form_type"]);
            if ($form_type == "delivery_setting") {
                $deliveryerCash["collect_max_carry"] = intval($_GPC["collect_max_carry"]);
                $deliveryerCash["permit_cancel"] = array("status_carry" => intval($_GPC["permit_cancel"]["status_carry"]));
                $deliveryerCash["permit_transfer"] = array(
                    "status_carry" => intval($_GPC["permit_transfer"]["status_carry"]),
                    "max_carry" => intval($_GPC["permit_transfer"]["max_carry"]),
                );
                $deliveryer_carry_fee_type = intval($_GPC["deliveryer_carry_fee_type"]);
                $deliveryer_carry_fee = 0;
                if ($deliveryer_carry_fee_type == 1) {
                    $deliveryer_carry_fee = floatval($_GPC["deliveryer_carry_fee_1"]);
                } else {
                    if ($deliveryer_carry_fee_type == 2) {
                        $deliveryer_carry_fee = floatval($_GPC["deliveryer_carry_fee_2"]);
                    } else {
                        if ($deliveryer_carry_fee_type == 3) {
                            $deliveryer_carry_fee = array("start_fee" => floatval($_GPC["deliveryer_carry_fee_3"]["start_fee"]), "start_km" => floatval($_GPC["deliveryer_carry_fee_3"]["start_km"]), "pre_km" => floatval($_GPC["deliveryer_carry_fee_3"]["pre_km"]), "max_fee" => floatval($_GPC["deliveryer_carry_fee_3"]["max_fee"]));
                        } else {
                            if ($deliveryer_carry_fee_type == 4) {
                                $deliveryer_carry_fee = floatval($_GPC["deliveryer_carry_fee_4"]);
                            }
                        }
                    }
                }
                $deliveryerCash["fee_delivery"] = array("carry" => array("deliveryer_fee_type" => $deliveryer_carry_fee_type, "deliveryer_fee" => $deliveryer_carry_fee));
            } else {
                if ($form_type == "getcash_setting") {
                    $deliveryerCash["fee_getcash"] = array("get_cash_fee_limit" => floatval($_GPC["fee_getcash"]["get_cash_fee_limit"]), "get_cash_fee_rate" => floatval($_GPC["fee_getcash"]["get_cash_fee_rate"]), "get_cash_fee_min" => floatval($_GPC["fee_getcash"]["get_cash_fee_min"]), "get_cash_fee_max" => floatval($_GPC["fee_getcash"]["get_cash_fee_max"]), "get_cash_period" => intval($_GPC["fee_getcash"]["get_cash_period"]));
                }
            }
            unset($deliveryerCash["get_cash_fee_limit"]);
            unset($deliveryerCash["get_cash_fee_rate"]);
            unset($deliveryerCash["get_cash_fee_min"]);
            unset($deliveryerCash["get_cash_fee_max"]);
            unset($deliveryerCash["get_cash_period"]);
            set_system_config(base64_decode("ZGVsaXZlcnkuY2FzaA=="), $deliveryerCash);
            $deliveryerCash["permit_cancel"] = iserializer($deliveryerCash["permit_cancel"]);
            $deliveryerCash["permit_transfer"] = iserializer($deliveryerCash["permit_transfer"]);
            $deliveryerCash["fee_delivery"] = iserializer($deliveryerCash["fee_delivery"]);
            $deliveryerCash["fee_getcash"] = iserializer($deliveryerCash["fee_getcash"]);
            $update = $deliveryerCash;
            if ($form_type == "delivery_setting") {
                unset($update["fee_getcash"]);
            } else {
                if ($form_type == "getcash_setting") {
                    $update = array("fee_getcash" => $update["fee_getcash"]);
                }
            }
            $sync = intval($_GPC["sync"]);
            if ($sync == 1) {
                pdo_update("hello_banbanjia_deliveryer", $update, array("uniacid" => $_W["uniacid"]));
            } else {
                if ($sync == 2) {
                    $deliveryer_ids = $_GPC["deliveryer_ids"];
                    foreach ($deliveryer_ids as $deliveryer_id) {
                        pdo_update("hello_banbanjia_deliveryer", $update, array("uniacid" => $_W["uniacid"], "id" => intval($deliveryer_id)));
                    }
                }
            }
            imessage(error(0, "搬运工设置成功"), referer(), "ajax");
        }
        mload()->lmodel("deliveryer");
        $deliveryers = deliveryer_all();
        include itemplate("config/deliveryer-cash");
    } else {
        if ($op == "extra") {
            $_W["page"]["title"] = "其他设置";
        }
    }
}
