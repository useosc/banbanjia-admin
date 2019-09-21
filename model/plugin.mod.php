<?php
defined('IN_IA') or exit('Access Denied');

class Ploader
{
    private $cache = array();
    public function func($name){
        global $_W;
        if (isset($this->cache["func"][$name])) {
            return true;
        }
        $file = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W["_plugin"]["name"] . "/function/" . $name . ".func.php";
        if (file_exists($file)) {
            include $file;
            $this->cache["func"][$name] = true;
            return true;
        }
        trigger_error("Invalid Helper Function /addons/hello_banbanjia/" . $_W["_plugin"]["name"] . "/function/" . $name . ".func.php", 256);
        return false;
    }
    public function model($name){
        global $_W;
        if(isset($this->cache['model'][$name])){
           return true;
        }
        $file = WE7_BANBANJIA_PLUGIN_PATH . (string) $name . "/model.php";
        if (!is_file($file)) {
            $file = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W["_plugin"]["name"] . "/model/" . $name . ".mod.php";
        }
        if (file_exists($file)) {
            include $file;
            $this->cache["model"][$name] = true;
            return true;
        }
        trigger_error("Invalid Helper Model /addons/hello_banbanjia/" . $_W["_plugin"]["name"] . "/model/" . $name . ".mod.php", 256);
        return false;
    }
    public function classs($name)
    {
        global $_W;
        if (isset($this->cache["class"][$name])) {
            return true;
        }
        $file = WE7_BANBANJIA_PLUGIN_PATH . (string) $_W["_plugin"]["name"] . "/class/" . $name . ".class.php";
        if (file_exists($file)) {
            include $file;
            $this->cache["class"][$name] = true;
            return true;
        }
        trigger_error("Invalid Helper Class /addons/hello_banbanjia/" . $_W["_plugin"]["name"] . "/class/" . $name . ".class.php", 256);
        return false;
    }
}

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
