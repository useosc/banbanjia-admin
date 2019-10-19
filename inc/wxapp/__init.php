<?php
header("Access-Control-Allow-Origin:*");
header('Access-Control-Allow-Methods:POST');
header('Access-Control-Allow-Headers:x-requested-with, content-type');
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel("common");
mload()->lfunc("wxapp"); //返回数据格式(api格式)
mload()->lmodel("member");
mload()->lmodel('store');
$_W["we7_hello_banbanjia"]["global"] = get_global_config(); //获取全局配置、鉴权
if ($_W["we7_hello_banbanjia"]["global"]["development"] == 1) { //开发模式
    ini_set("display_errors", "1");
    error_reporting(30719 ^ 8);
}
$_W["we7_hello_banbanjia"]["config"] = get_system_config();
$_config_mall = $_W['we7_hello_banbanjia']['config']['mall'];
if (empty($_config_mall['delivery_title'])) {
    $_config_mall['delivery_title'] = '平台专送';
}
$config_close = $_W['we7_hello_banbanjia']['config']['close'];
if($_W['ochannel'] == 'wxapp' || $_W['ochannel'] == 'wap'){
    $_W['we7_wxapp']['config'] = get_plugin_config('wxapp');
}
$config_wxapp = $_config_wxapp = $_W['we7_wxapp']['config'];
// var_dump($_W['we7_wxapp']['config']);exit;
// if ($_W['_ctrl'] == 'mall') {
//     if ($config_close['status'] == 2 || !$config_wxapp['basic']['status']) { //平台是否关闭
//         $config_close["tips"] = !empty($config_close["tips"]) ? $config_close["tips"] : "亲,平台休息中。。。";
//         imessage(error(-3000, $config_close["tips"]), "close", "ajax");
//     }
// }

$_W["role"] = "consumer";
$_W["role_cn"] = "下单顾客";
