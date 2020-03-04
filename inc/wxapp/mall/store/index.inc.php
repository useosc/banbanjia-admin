<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "公司详情";
$id = $sid = intval($_GPC["sid"]);
$config_mall = $_W["we7_hello_banbanjia"]["config"]["mall"];
$store = store_fetch($id);
if (empty($store)) {
    imessage(error(-1, "公司不存在或已经删除"), "", "ajax");
}

$store['is_favorite'] = is_favorite_store($sid,$_W['member']['uid']);
if (0 < $_W["member"]["uid"]) {
    $is_favorite = pdo_get("hello_banbanjia_store_favorite", array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"], "sid" => $id));
}

//浏览记录
$footmark = pdo_get("hello_banbanjia_member_footmark",array("uniacid" => $_W['uniacid'],'uid' => $_W['member']['uid'],"cid" => $sid, 'type' => 'store', 'stat_day' => date("Ymd")),array("id"));
if(empty($footmark)) {
    $insert = array("uniacid" => $_W['uniacid'],'uid' => $_W['member']['uid'],'cid' => $sid, 'type' => 'store','addtime' => TIMESTAMP, 'stat_day' => date("Ymd"));
    pdo_insert("hello_banbanjia_member_footmark",$insert);
}


$result = array('config' => $config_mall, 'store' => $store);
imessage(error(0, $result), "", "ajax");
