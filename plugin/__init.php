<?php
// error_reporting(E_ALL);
defined('IN_IA') or exit('Access Denied');
mload()->lmodel('plugin');
global $_W;
global $_GPC;
$name = $_W['_ctrl'];
$plugin = plugin_fetch($name);
$_W['_plugin'] = $plugin;
if (empty($plugin)) {
    imessage("插件不存在", referer(), "error");
}
if (!$plugin["status"]) {
    imessage("系统尚未开启该插件", referer(), "error");
}
// $status = plugin_account_has_permit($plugin["name"]);
// if (empty($status)) {
//     imessage("公众号没有使用该插件的权限", referer(), "error");
// }
$_W['_plugin']['config'] = $_config_plugin = get_plugin_config($_W["_plugin"]["name"]);
pload()->model($_W['_plugin']['name']);
if (!empty($_GPC["filter"])) {
    $_GPC["filter"] = json_decode(htmlspecialchars_decode($_GPC["filter"]), true);
    if (is_array($_GPC["filter"])) {
        foreach ($_GPC["filter"] as $key => $val) {
            $_GPC[$key] = $val;
        }
    }
}
?>