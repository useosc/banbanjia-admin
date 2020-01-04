<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
icheckauth();
if ($ta == 'list') {
    $time  = TIMESTAMP - 7776000;
    pdo_query("delete from " . tablename("hello_banbanjia_member_footmark") . " where uniacid = :uniacid and addtime < :time", array(":uniacid" => $_W['uniacid'], ":time" => $time));
    // $stores = pdo_
}
if($ta == 'del') {
    
}