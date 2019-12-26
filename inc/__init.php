<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lfunc("common"); //模板编译等函数相关
mload()->lclass("TyAccount"); //微信小程序账号
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
// var_dump($_plugins);exit;
//检测是否是插件
in_array($_W["_ctrl"], array_keys($_plugins)) and define("IN_PLUGIN", 1);
if (in_array($_W['_ctrl'], array('seckill', 'kanjia', 'pintuan', 'article', 'haodian'))) {
    define('IN_GOHOME_WPLUGIN', 1);
    if (!defined('IN_PLUGIN')) {
        define('IN_PLUGIN', 1);
    }
}
if (in_array($_W['_ac'], array('seckill', 'kanjia', 'pintuan', 'article', 'haodian'))) {
    define('IN_GOHOME_APLUGIN', 1);
}
if ($_W['_ctrl'] == 'gohome' || defined('IN_GOHOME_WPLUGIN')) {
    define('IN_GOHOME', 1);
}
if (strexists($_W["siteurl"], "web/index.php")) { //判断是否是后台管理页面
    define("IN_MANAGE", 1);
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
    if (defined('IN_MERCHANT')) {
        $file_path = WE7_BANBANJIA_PATH . 'inc/web/' . $_W['_ctrl'] . '/' . $_W['_ac'] . '/' . $_W['_op'] . '.inc.php';
        if(defined("IN_GOHOME_APLUGIN")){
            $file_path = WE7_BANBANJIA_PATH . 'inc/web/' . $_W['_ctrl'] . '/gohome/' . $_W['_ac'] . '/' . $_W['_op'] . '.inc.php';
        }
        if (!is_file($file_path)) {
            imessage("控制器 " . $_W["_ctrl"] . " 方法 " . $_W["_ac"] . "/" . $_W["_op"] . " 未找到!", "", "info");
        }
    } else {
        if (defined('IN_PLUGIN')) {    //插件入口
            $plugin_init = WE7_BANBANJIA_PLUGIN_PATH . "__init.php";
            require $plugin_init;
            $file_init = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W['_ctrl'] . '/inc/web/__init.php';
            $file_path = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W["_ctrl"] . "/inc/web/" . $_W["_ac"] . ".inc.php";
            if(defined("IN_AGENT")){

            }else{
                
            }
        }
    }

    if (is_file($file_init)) { //引入初始化文件
        require $file_init;
    }
    if (!is_file($file_path)) {
        imessage("控制器 " . $_W["_ctrl"] . " 方法 " . $_W["_ac"] . " 未找到!", "", "info");
    }
} else { //api接口路由
    // if (!in_array($_GPC['from'], array('wxapp','wap))) { //公众号
    //     $_W['ochannel'] = "wechat";
    //     $_W['channel'] = $_W['ochannel'];
    //     require WE7_BANBANJIA_PATH . 'inc/mobile/__init.php';
    //     $file_init = WE7_BANBANJIA_PATH . 'inc/mobile/' . $_W['_ctrl'] . '/__init.php';
    //     $file_path = WE7_BANBANJIA_PATH . "inc/mobile/" . $_W["_ctrl"] . "/" . $_W["_ac"] . "/" . $_W["_op"] . ".inc.php";

    //     if (is_file($file_init)) {
    //         require $file_init;
    //     }
    //     if (!is_file($file_path)) {
    //         imessage("控制器 " . $_W["_ctrl"] . " 方法 " . $_W["_ac"] . "/" . $_W["_op"] . " 未找到!", "close", "error");
    //     }
    // } 
    // if (!in_array($_GPC['from'], array('wxapp'))) { //h5
    //     $_W["ochannel"] = "wxapp";  //h5端
    //     $_W["channel"] = $_W["ochannel"];
    //     if ($_GPC["from"] == "web") {
    //         define("IN_WXAPP", 1);
    //     } 
    //     require WE7_BANBANJIA_PATH . "inc/wxapp/__init_web.php";
    //     $file_init = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W["_ctrl"] . "/__init.php";
    //     $file_path = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W["_ctrl"] . "/" . $_W["_ac"] . "/" . $_W["_op"] . ".inc.php";
    //     if (is_file($file_init)) {
    //         require $file_init;
    //     }
    //     if (!is_file($file_path)) {
    //         imessage(error(-1, "控制器web " . $_W["_ctrl"] . " 方法 " . $_W["_ac"] . "/" . $_W["_op"] . " 未找到!"), "", "ajax");
    //     }
    // } else {
    $_W["ochannel"] = "wxapp";  //小程序端
    if ($_GPC["from"] == "wxapp") {
        define("IN_WXAPP", 1);
    } else {
        // if ($_GPC['from'] == 'web') { //web手机端
        //     $_W['ochannel'] = 'web';
        //     define('IN_WEB', 1);
        // }
        if ($_GPC['from'] == 'wap') { //wap手机端
            $_W['ochannel'] = 'wap';
            define('IN_WAP', 1);
        }
        if ($_GPC['from'] == 'pc') { //pc前端
            $_W['ochannel'] = 'pc';
            define('IN_PC', 1);
        }
    }
    $_W["channel"] = $_W["ochannel"];

    require WE7_BANBANJIA_PATH . "inc/wxapp/__init.php";
    $file_init = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W["_ctrl"] . "/__init.php";
    $file_path = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W["_ctrl"] . "/" . $_W["_ac"] . "/" . $_W["_op"] . ".inc.php";
    if ($_W['_ctrl'] == 'plateform') {
        define('IN_PLATEFORM', 1);
        $file_init = "";
        require WE7_BANBANJIA_PATH . "inc/wxapp/plateform/__init.php";
        if (in_array($_W['_ac'], array_keys($_plugins))) {
            $file_init = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W['_ctrl'] . "/plugin/" . $_W['_ac'] . "/__init.php";
            $file_init = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W['_ctrl'] . "/plugin/" . $_W['_ac'] . '/' . $_W['_op'] . ".inc.php";
        }
    } else {
        if ($_W['_ctrl'] == 'manage') {
            if (defined('IN_GOHOME_APLUGIN')) {
                $file_path = WE7_BANBANJIA_PATH . "inc/wxapp/" . $_W['_ctrl'] . "/gohome/" . $_W['_ac'] . '/' . $_W['_op'] . '.inc.php';
            }
        } else {
            if (defined("IN_PLUGIN")) {
                $plugin_init = WE7_BANBANJIA_PLUGIN_PATH . "__init.php";
                require $plugin_init;
                $file_init = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W['_ctrl'] . '/inc/wxapp/__init.php';
                $file_path = $file_init = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W['_ctrl'] . '/inc/wxapp/' . $_W['_ac'] . '.inc.php';
            }
        }
    }

    if (is_file($file_init)) {
        require $file_init;
    }
    if (!is_file($file_path)) {
        imessage(error(-1, "控制器wxapp " . $_W["_ctrl"] . " 方法 " . $_W["_ac"] . "/" . $_W["_op"] . " 未找到!"), "", "ajax");
    }

    // }
}

require $file_path; //引入主文件
