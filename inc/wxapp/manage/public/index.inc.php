<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";

if($ta == 'clerklist') {
    $list = pdo_getall("hello_banbanjia_store_clerk",array("uniacid" => $_W['uniacid'],"sid" => $_W['sid']));
    imessage(error(0,$list),'','ajax');
}