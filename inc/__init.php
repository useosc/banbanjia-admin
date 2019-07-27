<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lfunc("common"); //模板编译等函数相关
// mload()->lclass("TyAccount");
$routers = str_replace("//", "/", trim($_GPC["r"], "/"));
$routers = explode(".", $routers);
$_W["_do"] = !empty($_W["_do"]) ? $_W["_do"] : trim($_GPC["do"]);
$_W["_ctrl"] = !empty($_W["_ctrl"]) ? $_W["_ctrl"] : trim($_GPC["ctrl"]); //控制器
$_W["_ac"] = trim($_GPC["ac"]); //方法
$_W["_op"] = trim($_GPC["op"]); //参数
$_W["_ta"] = trim($_GPC["ta"]);
$_W["_router"] = implode("/", array($_W["_ctrl"], $_W["_ac"], $_W["_op"])); //路由
$_plugins = pdo_getall("hello_banbanjia_plugin", array(), array("name", "title"), "name"); //插件
$_W["_plugins"] = $_plugins;
in_array($_W["_ctrl"], array_keys($_plugins)) and define("IN_PLUGIN", 1);

if (strexists($_W["siteurl"], "web/index.php")) { //判断是否是后台管理页面
    define("IN_MANAGE", 1);
} else {
    if (strexists($_W["siteurl"], "web/wagent.php")) {
        define("IN_PLUGIN", 1);
        define("IN_AGENT", 1);
    }
}

if (defined("IN_SYS")) { //web/index 后台入口文件进入
    if (empty($_W["uniacid"])) {
        message("公众号信息错误，请重新管理公众号", url("account/display"), "info");
    }
    if ($_W["_ctrl"] == "store") {
        define("IN_MERCHANT", 1);
    }
    if (empty($_W["_ctrl"])) {
        $_W["_ctrl"] = "dashboard";
        $_W["_ac"] = "index";
    }
    require WE7_BANBANJIA_PATH . "inc/web/__init.php"; //后台初始化
    $file_init = WE7_BANBANJIA_PATH . "inc/web/" . $_W["_ctrl"] . "/__init.php";
    $file_path = WE7_BANBANJIA_PATH . "inc/web/" . $_W["_ctrl"] . "/" . $_W["_ac"] . ".inc.php";

    if (is_file($file_init)) { //引入初始化文件
        require $file_init;
    }
} else { //api接口路由
    $_W["ochannel"] = "wxapp";
    $_W["channel"] = $_W["ochannel"];
    if ($_GPC["from"] == "wxapp") {
        defined("IN_WXAPP", 1);
    }
    require WE7_BANBANJIA_PATH . "inc/wxapp/__init.php";
    $file_init = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W["_ctrl"] . "/__init.php";
    $file_path = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W["_ctrl"] . "/" . $_W["_ac"] . "/" . $_W["_op"] . ".inc.php";
    if (is_file($file_init)) {
        require $file_init;
    }
    if (!is_file($file_path)) {
        imessage(error(-1, "控制器wxapp " . $_W["_ctrl"] . " 方法 " . $_W["_ac"] . "/" . $_W["_op"] . " 未找到!"), "", "ajax");
    }
}

require $file_path; //引入主文件
