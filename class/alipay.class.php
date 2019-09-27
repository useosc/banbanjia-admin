<?php
defined("IN_IA") or exit("Access Denied");
class Alipay
{
    public $alipay = NULL;
    public function __construct($pay_type = 'wap')
    {
        global $_W;
        $alipay = $_W['we7_hello_banbanjia']['config']['payment']['alipay'];
        if ($pay_type == 'h5app') {
            $alipay = $_W['we7_hello_banbanjia']['config']['payment']['app_alipay'];
        }
        $this->alipay = array('app_id' => $alipay['appid'], 'rsa_type' => empty($alipay['rsa_type']) ? 'RSA' : $alipay['rsa_type']);
        $this->cert = array("private_key" => $alipay["private_key"], "public_key" => $alipay["public_key"]);
    }
    public function array2url($params, $force = false)
    {
        $str = "";
        foreach ($params as $key => $val) {
            if ($force && empty($val)) {
                continue;
            }
            $str .= (string) $key . "=" . $val . "&";
        }
        $str = trim($str, "&");
        return $str;
    }
    public function buildSign($params) //生成签名
    {
        unset($params['sign']);
        ksort($params);
        $string = $this->array2url($params,true);
        $priKey = file_get_contents(MODULE_ROOT . "/cert/" . $this->cert["private_key"] . "/private_key.pem");
        $res = openssl_get_privatekey($priKey);
        if ($params["sign_type"] == "RSA") {
            openssl_sign($string, $sign, $res);
        } else {
            openssl_sign($string, $sign, $res, OPENSSL_ALGO_SHA256);
        }
        openssl_free_key($res);
        $sign = base64_encode($sign);
        return $sign;
    }
    public function checkCert()
    {
        global $_W;
        if (empty($this->cert["private_key"]) || empty($this->cert["public_key"])) {
            return error(-1, "支付宝支付证书不完整");
        }
        return true;
    }
}
