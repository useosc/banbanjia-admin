<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "payment";
if ($op == 'payment') {
    $_W["page"]["title"] = "支付方式";
    if ($_W['ispost']) {
        load()->func('file');
        $config_old_payment = $_config['payment'];
        $config_payment = array(
            "wechat" => array(
                "appid" => trim($_GPC['wechat']['core']['appid']),
                "appsecret" => trim($_GPC['wechat']['core']['appsecret']),
                "mchid" => trim($_GPC['wechat']['core']['mchid']),
                "apikey" => trim($_GPC["wechat"]["core"]["apikey"]),
                "partner" => trim($_GPC["wechat"]["core"]["partner"]),
                "key" => trim($_GPC["wechat"]["core"]["key"]),
                "signkey" => trim($_GPC["wechat"]["core"]["signkey"]),
                "apiclient_cert" => $config_old_payment["wechat"]["core"]["apiclient_cert"],
                "apiclient_key" => $config_old_payment["wechat"]["core"]["apiclient_key"],
                "rootca" => $config_old_payment["wechat"]["core"]["rootca"]
            ),
            "alipay" => array(
                "account" => trim($_GPC["alipay"]["account"]),
                "partner" => trim($_GPC["alipay"]["partner"]),
                "secret" => trim($_GPC["alipay"]["secret"]),
                "appid" => trim($_GPC["alipay"]["appid"]),
                "rsa_type" => trim($_GPC["alipay"]["rsa_type"]),
                "private_key" => $config_old_payment["alipay"]["private_key"],
                "public_key" => $config_old_payment["alipay"]["public_key"]
            ),
            "h5_wechat" => array(
                "appid" => trim($_GPC["h5"]["appid"]),
                "appsecret" => trim($_GPC["h5"]["appsecret"]),
                "mchid" => trim($_GPC["h5"]["mchid"]),
                "apikey" => trim($_GPC["h5"]["apikey"]),
                "apiclient_cert" => $config_old_payment["h5_wechat"]["apiclient_cert"],
                "apiclient_key" => $config_old_payment["h5_wechat"]["apiclient_key"],
                "rootca" => $config_old_payment["h5_wechat"]["rootca"]
            ),
        );

        $keys = array('private_key','public_key'); //支付宝公钥私钥
        foreach($keys as $key){
            if(!empty($_GPC['alipay'][$key])){
                $text = $_GPC['alipay'][$key];
                $text = str_replace("\\r","",$text);
                $text = str_replace("\\n","",$text);
                $text = implode(str_split($text,64),"\n");
                if($key == 'private_key'){
                    $text = "-----BEGIN RSA PRIVATE KEY-----\n" . $text . "\n-----END RSA PRIVATE KEY-----";
                }else{
                    $text = "-----BEGIN PUBLIC KEY-----\n" . $text . "\n-----END PUBLIC KEY-----";
                }
                @unlink(MODULE_ROOT . "/cert/" . $config_payment['alipay'][$key] . '/' . $key . '.pem');
                @rmdir(MODULE_ROOT . "/cert/" . $config_payment["alipay"][$key]);
                $name = random(10);
                $status = ifile_put_contents('cert/' . $name . '/' . $key . '.pem',$text);
                $config_payment['alipay'][$key] = $name;
            }
        }

        set_system_config('payment', $config_payment);
        imessage(error(0, '支付方式设置成功'), referer(), 'ajax');
    }
    $payment = $_config['payment'];
    include itemplate('config/payment');
}
