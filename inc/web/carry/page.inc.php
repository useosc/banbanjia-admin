<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";

$_W['page']['title'] = '国内搬家设置';
// $freepage = pdo_fetch("SELECT * FROM " . tablename("hello_banbanjia_domestice_page") . " WHERE uniacid = :uniacid and type = :type",array(":type" => "free",":uniacid" => $_W['uniacid']));
// if($_W['ispost']){
//     $insert = array('uniacid' => $_W['uniacid'],'name' => $_GPC['data'][''])
// }

if(!empty($freepage)){
    $freepage['data'] = json_decode(base64_decode($freepage["data"]), true);
}

include itemplate('carry/freepage');