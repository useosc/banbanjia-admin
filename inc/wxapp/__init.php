<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel("common");
mload()->lfunc("wxapp"); //返回数据格式(api格式)
mload()->lmodel("member");
$_W["we7_hello_banbanjia"]["global"] = get_global_config(); //获取全局配置、鉴权
if($_W["we7_hello_banbanjia"]["global"]["development"] ==1){ //开发模式
    ini_set("display_errors","1");
    error_reporting(30719 ^ 8);
}
$_W["we7_hello_banbanjia"]["config"] = get_system_config();

$_W["role"] = "consumer";
$_W["role_cn"] = "下单顾客";

?>