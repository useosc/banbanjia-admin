<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "公司详情";
$id = $sid = intval($_GPC["sid"]);
$config_mall = $_W["we7_hello_banbanjia"]["config"]["mall"];
$store = store_fetch($id);
$is_favorite = pdo_get("hello_banbanjia_store_favorite", array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"], "sid" => $sid));
if (0 < $_W["member"]["uid"]) {
    $is_favorite = pdo_get("hello_banbanjia_store_favorite", array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"], "sid" => $id));
}
$result = array('config' => $config_mall, 'store' => $store, 'is_favorite' => $is_favorite);
imessage(error(0, $result), "", "ajax");
