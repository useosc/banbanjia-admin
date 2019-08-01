<?php
defined('IN_IA') or exit('Access Denied');

function plugin_fetch($name) //获取插件信息
{
    $condition = " where name = :name";
    $params = array(":name" => $name);
    $plugin = pdo_fetch("select * from " . tablename('hello_banbanjia_plugin') . $condition, $params);
    return $plugin;
}
//判断公众号是否有权限使用
function plugin_account_has_permit($name)
{
    $permit = get_account_permit();
    if(empty($permit)){
        return true;
    }
    
}