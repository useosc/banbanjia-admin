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
    if (empty($permit)) {
        return true;
    }
}
//插件类型
function plugin_types()
{
    return array("biz" => array("name" => "biz", "title" => "业务类"), "activity" => array("name" => "activity", "title" => "营销类"), "tool" => array("name" => "tool", "title" => "工具类"), "help" => array("name" => "help", "title" => "辅助类"));
}
//获取全部插件
function plugin_fetchall($status = 1)
{
    $condition = " where is_show = 1";
    $params = array();
    if (!empty($status)) {
        $condition .= " and status = :status";
        $params[":status"] = $status;
    }
    $condition .= " order by displayorder desc";
    $plugins = pdo_fetchall("select * from " . tablename("hello_banbanjia_plugin") . $condition, $params, "name");
    return $plugins;
}
