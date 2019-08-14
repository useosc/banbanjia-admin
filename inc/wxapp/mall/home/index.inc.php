<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
$config_mall = $_W['w7_hello_banbanjia']['config']['mall'];
if(defined("IN_WXAPP")){
    icheckauth();
}

if($ta == 'index'){
    $config_app_customer = $_W['we7_hello_banbanjia']['config']['app']['customer'];
    mload()->lmodel('plugin');
    $default_location = array();
    if (empty($_GPC["lat"]) || empty($_GPC["lng"])) {
        $config_carry = $_W["we7_hello_banbanjia"]["config"]["carry"]["range"];
        if (!empty($config_carry["map"]["location_x"]) && !empty($config_carry["map"]["location_y"])) {
            $_GPC["lat"] = $config_carry["map"]["location_x"];
            $_GPC["lng"] = $config_carry["map"]["location_y"];
            $default_location = array("location_x" => $config_carry["map"]["location_x"], "location_y" => $config_carry["map"]["location_y"], "address" => $config_carry["city"]);
        }
    }
}