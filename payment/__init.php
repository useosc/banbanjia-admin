<?php
defined("IN_IA") or exit("Access Denied");

function get_system_config($key = '',$uniacid = -1)
{
    global $_W;
    if ($uniacid == -1) {
        $uniacid = intval($_W["uniacid"]);
    }
    $config = pdo_get("hello_banbanjia_config", array("uniacid" => $uniacid), array("sysset", "pluginset", "id"));
    if (empty($config["id"])) {
        $init_config = array("uniacid" => $_W["uniacid"]);
        pdo_insert("hello_banbanjia_config", $init_config);
        return array();
    }
    $sysset = iunserializer($config["sysset"]);
    if (!is_array($sysset)) {
        $sysset = array();
    }
    $pluginset = iunserializer($config["pluginset"]);
    if (!is_array($pluginset)) {
        $pluginset = array();
    }
    if (empty($key)) {
        return $sysset;
    }
    $keys = explode(".", $key);
    $counts = count($keys);
    if ($counts == 1) {
        return $sysset[$key];
    }
    if ($counts == 2) {
        return $sysset[$keys[0]][$keys[1]];
    }
    if ($counts == 3) {
        return $sysset[$keys[0]][$keys[1]][$keys[2]];
    }
}

function get_plugin_config($key = "")
{
    global $_W;
    $_W["uniacid"] = intval($_W["uniacid"]);
    $config = pdo_get("hello_banbanjia_config", array("uniacid" => $_W["uniacid"]), array("pluginset"));
    if (empty($config)) {
        return array();
    }
    $pluginset = iunserializer($config["pluginset"]);
    if (!is_array($pluginset)) {
        return array();
    }
    if (empty($key)) {
        return $pluginset;
    }
    $keys = explode(".", $key);
    $plugin = $keys[0];
    if (!empty($plugin)) {
        $config_plugin = $pluginset[$plugin];
        if (!is_array($config_plugin)) {
            return array();
        }
        $count = count($keys);
        if ($count == 2) {
            return $config_plugin[$keys[1]];
        }
        if ($count == 3) {
            return $config_plugin[$keys[1]][$keys[2]];
        }
        return $config_plugin;
    }
}