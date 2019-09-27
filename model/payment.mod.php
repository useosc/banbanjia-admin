<?php
defined("IN_IA") or exit("Access Denied");
function alipay_build($params, $alipay = array())
{
    global $_W;
    $config_paycallback = $_W["we7_hello_banbanjia"]["config"]["paycallback"];
    $notify_use_http = intval($config_paycallback["notify_use_http"]);
    load()->func("communication");
    $trade_type = $alipay["trade_type"];
    $set = array();
    $tid = $params['uniontid'];
    if($trade_type == 'wap'){ //手机网站支付
        $set['app_id'] = $alipay['appid'];
        $set['method'] = 'alipay.trade.wap.pay';
        $set['charset'] = 'utf-8';
        $set['sign_type'] = $alipay['rsa_type'];
        $set['timestamp'] = date('Y-m-d H:i:s');
        $set['version'] = '1.0';
        $set['notify_url'] = (WE7_BANBANJIA_ISHTTPS && $notify_use_http ? WE7_BANBANJIA_URL_NOHTTPS : WE7_BANBANJIA_URL) . "payment/alipay/notify.php";
        $biz_content = array(
            'subject' => $params['title'],
            'out_trade_no' => $tid,
            'total_amount' => $params['fee'],
            'product_code' => 'QUICK_WAP_WAY'
        );
        $set["biz_content"] = json_encode($biz_content);
        ksort($set);
        mload()->lclass('alipay');
        $alipayClass = new Alipay('wap');
        $set['sign'] = $alipayClass->bulidSign($set);
        $string = '';
        foreach ($set as $key => $value) {
            $value = rawurlencode($value);
            $string .= (string) $key . "=" . $value . "&";
        }
        $string = rtrim($string, "&");
        return array("orderInfo" => $string);
    }
 }
