<?php
defined("IN_IA") or exit("Access Denied");

if (!function_exists("get_system_config")) { //获取系统配置
    function get_system_config($key = "", $uniacid = -1)
    {
        global $_W;
        if ($uniacid == -1) {
            $uniacid = intval($_W["uniacid"]);
        }
        $config = pdo_get("hello_banbanjia_config", array("uniacid" => $uniacid), array("sysset", "pluginset", "id"));
        if (empty($config["id"])) {
            $init_config = array("uniacid" => $uniacid);
            pdo_insert("hello_banbanjia_config", $init_config);
            return array();
        }
        $sysset = iunserializer($config["sysset"]); //反序列化系统设置
        if (!is_array($sysset)) {
            $sysset = array();
        }
        $pluginset = iunserializer($config["pluginset"]);
        if (!is_array($pluginset)) {
            $pluginset = array();
        }
        if (!empty($sysset["platform"]["logo"])) { //平台logo
            $sysset["platform"]["logo"] = tomedia($sysset["platform"]["logo"]);
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
}

function get_global_config($key = "") //获取全局配置

{
    $result = get_system_config($key, 0);
    return $result;
}

function check_permit($permit, $redirct = false)
{
    global $_W;
    $redircts = array("common", "store");
    if (in_array($_W["_ctrl"], $redircts)) {
        return true;
    }
    if ($_W["isfounder"] == 1 || $_W["permits"] == "all") {
        return true;
    }
    if (empty($_W["permits"])) {
        return false;
    }
    if ($permit == "plugin.index") {
        return true;
    }
    if (in_array($permit, $_W["permits"])) {
        return true;
    }
    if (defined("IN_PLATEFORM")) {
        $all_permits = array();
        if ($_W["plateformer"]["usertype"] == "agenter") {
            $all_permits = get_agent_permits();
        } else {
            $all_permits = get_all_permits(true);
        }
        if (!in_array($permit, $all_permits)) {
            return true;
        }
    }
    if ($_W["role"] == "agent_operator") {
        $extrapermit = array("agent.loginout", "agent.setting", "oauth.login");
        if (in_array($permit, $extrapermit)) {
            return true;
        }
    }
    if ($redirct) {
        $permits_init = array("dashboard.index", "merchant.store", "order.takeout", "statcenter.takeout", "paycenter.paybill", "merchant.store", "service.comment", "deliveryer.account", "clerk.account", "member.index", "config.mall", "errander.index", "bargain.index", "deliveryCard.index", "qianfanApp.index", "majiaApp.index", "shareRedpacket.index", "freeLunch.index", "diypage.index", "ordergrant.index", "superRedpacket.index", "creditshop.index", "agent.index", "wheel.index", "gohome.index", "svip.index", "spread.index", "advertise.index", "cloudGoods.index", "mealRedpacket.index", "storebd.index", "zhunshibao.index");
        if (in_array($permit, $permits_init)) {
            $permit_arr = explode(".", $permit);
            foreach ($_W["permits"] as $row) {
                if (strexists($row, (string) $permit_arr[0] . ".")) {
                    $permit = explode(".", $row);
                    header("location:" . iurl((string) $permit["0"] . "/" . $permit["1"]));
                    exit;
                }
            }
            return false;
        }
    }
    return false;
}
//插件配置
if (!function_exists('get_plugin_config')) {
    function get_plugin_config($key = '')
    {
        global $_W;
        $_W['uniacid'] = intval($_W['uniacid']);
        $config = pdo_get('hello_banbanjia_config', array('uniacid' => $_W['uniacid']), array('pluginset'));
        if (empty($config)) {
            return array();
        }
        $pluginset = iunserializer($config["pluginset"]);
        if (empty($key)) {
            return $pluginset;
        }
    }
}
