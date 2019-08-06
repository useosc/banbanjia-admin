<?php
defined("IN_IA") or exit("Access Denied");
function popEncode($str){
   $result = urlencode($str); 
   $result = preg_replace("/\\+/", "%20", $result);
   $result = preg_replace("/\\*/", "%2A", $result);
   $result = preg_replace("/%7E/", "~", $result);
   return $result;
}
function sms_send($tpl, $mobile, $content, $type = 'aliyun')
{
    global $_W;
    $config_sms = $_W["we7_hello_banbanjia"]["config"]["sms"];
    if (!is_array($config_sms["set"])) {
        return error(-1, "平台没有设置短信参数");
    }
    if (empty($config_sms["set"]["status"])) {
        return error(-1, "平台已关闭短信功能");
    }
    if ($type == 'aliyun') { //阿里云短信
        date_default_timezone_set("GMT");
        $post = array("PhoneNumbers" => $mobile, "SignName" => $config_sms["set"]["sign"], "TemplateCode" => trim($tpl), "TemplateParam" => json_encode($content), "OutId" => "", "RegionId" => "cn-hangzhou", "AccessKeyId" => $config_sms["set"]["key"], "Format" => "json", "SignatureMethod" => "HMAC-SHA1", "SignatureVersion" => "1.0", "SignatureNonce" => uniqid(), "Timestamp" => date("Y-m-d\\TH:i:s\\Z"), "Action" => "SendSms", "Version" => "2017-05-25");
        ksort($post);
        $str = "";
        foreach($post as $key => $value){
            $str .= "&" . popEncode($key) . "=" . popEncode($value);
        }
        $stringToSign = "GET" . "&%2F&" . popEncode(substr($str, 1));
        $signature = base64_encode(hash_hmac("sha1", $stringToSign, (string) $config_sms["set"]["secret"] . "&", true));
        $post["Signature"] = $signature;
        $url = "http://dysmsapi.aliyuncs.com/?" . http_build_query($post);
        $result = ihttp_get($url);
        if (is_error($result)) {
            return $result;
        }
        $result = @json_decode($result["content"], true);
        if ($result["Code"] != "OK") {
            return error(-1, $result["Message"]);
        }
    }
    return true;
}
