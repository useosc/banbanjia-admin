<?php
defined("IN_IA") or exit("Access Denied");
mload()->lmodel("store");
global $_W;
global $_GPC;
$_W['page']['title'] = '搜索';
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
$config_mall = $_W["we7_hello_banbanjia"]["config"]["mall"];
$store_category = store_fetch_category();
if ($ta == "index") {

    $orderbys = store_orderbys();
    // $discounts = store_discounts();
    // $result = array("config" => $config_mall, "stores" => store_filter(), "orderbys" => $orderbys, "discounts" => $discounts, "carousel" => $carousel);
    $result = array("config" => $config_mall, "stores" => store_filter(),'orderbys' => $orderbys);
    imessage(error(0, $result), "", "ajax");
} 