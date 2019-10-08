<?php
// error_reporting(0);
error_reporting(E_ALL);
define('IN_MOBILE', true);
//记录到文件
function logs($data)
{
    $tt = '--------------' . date("Y-m-d H:i:s") . '
';
    $data = is_array($data) ? print_r($data, true) : $data;
    $tt .= $data . '
';
    file_put_contents('./logs/log.txt', $tt, FILE_APPEND);
}

if (!empty($_POST)) {
    require "../../../../framework/bootstrap.inc.php";
    require "../../../../addons/hello_banbanjia/payment/__init.php";
    require "../../../../addons/hello_banbanjia/class/TyAccount.class.php";
    require "../../../../addons/hello_banbanjia/class/alipay.class.php";
    require "../../../../addons/hello_banbanjia/defines.php";
    logs($_POST);
    $out_trade_no = $_POST["out_trade_no"];
    $body = explode(":", $_POST["body"]);
    $_W["weid"] = intval($body[0]);
    $_W["uniacid"] = $_W["weid"];
    $_W["account"] = uni_fetch($_W["uniacid"]);
    $_W["uniaccount"] = $_W["account"];
    $_W["acid"] = $_W["uniaccount"]["acid"];
    $_W['we7_hello_banbanjia']['config'] = get_system_config();
    $type = trim($body[1]) ? trim($body[1]) : "wap";
    $payment_from = trim($body[2]);
    if ($payment_from == "plugincenter") {
        $config_payment = get_plugin_config("plugincenter.pay_type");
    } else {
        $config_payment = get_system_config("payment");
    }
    // var_dump($config_payment);exit;
    // logs($config_payment);
    if ($type == "wap") {
        $config_alipay = $config_payment["alipay"];
        if (empty($config_alipay)) {
            exit("fail");
        }
        $prepares = array();
        foreach ($_POST as $key => $value) {
            if ($key != "sign" && $key != "sign_type" && !empty($value)) {
                $prepares[] = (string) $key . "=" . $value;
            }
        }
        sort($prepares);

        $string = implode("&", $prepares);

        load()->lclass('alipay');
        $alipayClass = new Alipay('wap');
        $params['string'] = $string;
        $params['sign'] = $_POST['sign'];
        $params['sign_type'] = $_POST['sign_type'];
        $is_right = $alipayClass->checkSign($params);
    }
    if ($is_right || $rsaverify) {
        $_POST["query_type"] = "notify";
        $log = pdo_fetch("SELECT * FROM " . tablename("core_paylog") . " WHERE `uniontid`=:uniontid", array(":uniontid" => $out_trade_no));
        if (!empty($log) && $log["status"] == "0" && ($_POST["total_fee"] == $log["card_fee"] || $_POST["total_amount"] == $log["card_fee"])) {
            $log["transaction_id"] = $_POST["trade_no"];
            $log["type"] = "alipay";
            $record = array();
            $record["status"] = "1";
            $record["type"] = "alipay";
            pdo_update("core_paylog", $record, array("plid" => $log["plid"]));
            $site = WeUtility::createModuleSite($log["module"]);
            if (!is_error($site)) {
                $method = "payResult";
                if (method_exists($site, $method)) {
                    $ret = array();
                    $ret["uniacid"] = $log["uniacid"];
                    $ret["acid"] = $log["acid"];
                    $ret["result"] = "success";
                    $ret["type"] = $log["type"];
                    $ret["channel"] = $type;
                    $ret["from"] = "notify";
                    $ret["tid"] = $log["tid"];
                    $ret["uniontid"] = $log["uniontid"];
                    $ret["transaction_id"] = $log["transaction_id"];
                    $ret["user"] = $log["openid"];
                    $ret["fee"] = $log["fee"];
                    $ret["is_usecard"] = $log["is_usecard"];
                    $ret["card_type"] = $log["card_type"];
                    $ret["card_fee"] = $log["card_fee"];
                    $ret["card_id"] = $log["card_id"];
                    $site->{$method}($ret);
                    exit("success");
                }
            }
        }
    }
}
exit('fail');
