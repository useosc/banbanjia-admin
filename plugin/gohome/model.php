<?php
defined("IN_IA") or exit("Access Denied");

function gohome_update_activity_flow($activity_type, $goods_id, $type)
{
    global $_W;
    if (!in_array($type, array("looknum", "sharenum","answernum"))) {
        return false;
    }
    $routers = array(
        "pintuan" => array("table" => "hello_banbanjia_pintuan_goods"),
        "kanjia" => array("table" => "hello_banbanjia_kanjia"),
        "seckill" => array("table" => "hello_banbanjia_seckill_goods"),
        "article" => array("table" => "hello_banbanjia_article_information"),
        "ask" => array("table" => "hello_banbanjia_ask_information")
    );
    $router = $routers[$activity_type];
    pdo_query("UPDATE " . tablename($router["table"]) . " set " . $type . " = " . $type . " + 1 WHERE uniacid = :uniacid AND id = :id", array(":uniacid" => $_W["uniacid"], ":id" => $goods_id));
    return true;
}
