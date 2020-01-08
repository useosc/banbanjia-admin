<?php
defined("IN_IA") or exit("Access Denied");
mload()->lmodel('page');
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
$config_mall = $_W['we7_hello_banbanjia']['config']['mall'];
if(defined("IN_WXAPP")){
    icheckauth();
    $_config_wxapp_basic = $_config_wxapp["basic"];
    $_W["we7_hello_banbanjia"]["config_mall"] = $config_mall;
}else{

}

if($ta == 'index'){
    mload()->lmodel('plugin');

    mload()->lmodel('diy');
    if($_config_wxapp['diy']['use_diy_home'] !=1 ){

    }else{
        $pageOrid = $_config_wxapp['diy']['shopPage']['home'];
        if(empty($pageOrid)){
            imessage(error(-1,'未设置首页DIY页面'),'','ajax');
        }
    }
    $page = get_wxapp_diy($pageOrid,true,array("pagetype"=>$pagetype,"pagepath" => "home"));
    if(empty($page)){
        imessage(error(-1,'页面不能为空'),'','ajax');
    }


    $default_location = array();
    if (empty($_GPC["lat"]) || empty($_GPC["lng"])) {
        $config_carry = $_W["we7_hello_banbanjia"]["config"]["carry"]["range"];
        if (!empty($config_carry["map"]["location_x"]) && !empty($config_carry["map"]["location_y"])) {
            $_GPC["lat"] = $config_carry["map"]["location_x"];
            $_GPC["lng"] = $config_carry["map"]["location_y"];
            $default_location = array("location_x" => $config_carry["map"]["location_x"], "location_y" => $config_carry["map"]["location_y"], "address" => $config_carry["city"]);
        }
    }

    $result = array("is_use_diy" => 1,"config" => $config_mall,"config_wxapp" => $_config_wxapp,"diy" =>$page,"default_location" => $default_location);

    imessage(error(0,$result),'','ajax');
    return 1;
}