<?php
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
mload()->lmodel('sms');
global $_W;
global $_GPC;
$today = date("Ymd");
$cache_key = "we7_hello_banbanjia:sms:" . $_W["uniacid"] . ":" . $today;
$sentTimes = cache_read($cache_key);
$sentTimes = intval($sentTimes);
if (200 <= $sentTimes) {
    imessage(error(-1, "今日获取验证码次数已达最高限制"), "", "ajax");
}
$mobile = trim($_GPC["mobile"]);
if ($mobile == "") {
    imessage(error(-1, "请输入手机号"), "", "ajax");
}
if (!is_validMobile($mobile)) {
    imessage(error(-1, "手机号格式错误"), "", "ajax");
}
$sql = "DELETE FROM " . tablename("uni_verifycode") . " WHERE `createtime`<" . (TIMESTAMP - 1800);
pdo_query($sql);
$sql = "SELECT * FROM " . tablename("uni_verifycode") . " WHERE `receiver`=:receiver AND `uniacid`=:uniacid";
$pars = array();
$pars[":receiver"] = $mobile;
$pars[":uniacid"] = $_W["uniacid"];
$row = pdo_fetch($sql, $pars);
$record = array();
if (!empty($row)) {
    if (80 <= $row["total"]) {
        imessage(error(-1, "您的操作过于频繁,请稍后再试"), "", "ajax");
    }
    $code = $row["verifycode"];
    $record["total"] = $row["total"] + 1;
}else {
    $code = random(6, true);
    $record["uniacid"] = $_W["uniacid"];
    $record["receiver"] = $mobile;
    $record["verifycode"] = $code;
    $record["total"] = 1;
    $record["createtime"] = TIMESTAMP;
}
if (!empty($row)) {
    pdo_update("uni_verifycode", $record, array("id" => $row["id"]));
} else {
    pdo_insert("uni_verifycode", $record);
}
$content = array("code" => $code);
$config_sms = $_W["we7_hello_banbanjia"]["config"]["sms"]["template"];
$result = sms_send($config_sms["verify_code_tpl"], $mobile, $content);
if (is_error($result)) {
    slog("alidayuSms", "阿里云短信通知验证码", $content, $result["message"]);
    imessage(error(-1, $result["message"]), "", "ajax");
}
$sentTimes++;
cache_write($cache_key, $sentTimes);
imessage(error(0, "success"), "", "ajax");