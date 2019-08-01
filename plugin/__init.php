<?php

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

?>