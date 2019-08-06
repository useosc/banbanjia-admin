<?php
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$today = date("Ymd");
$cache_key = "we7_hello_banbanjia:sms:" . $_W["uniacid"] . ":" . $today;
$sentTimes = cache_read($cache_key);
$sentTimes = intval($sentTimes);
if (200 <= $sentTimes) {
    imessage(error(-1, "今日获取验证码次数已达最高限制"), "", "ajax");
}