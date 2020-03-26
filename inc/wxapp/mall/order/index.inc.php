<?php


defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckAuth();
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if($ta == 'create'){
    $id = intval($_GPC['id']);
    $params = json_decode(htmlspecialchars_decode($_GPC['extra']),true);
    if(empty($params)){
        imessage(error(-1,'参数错误'),'','ajax');
    }
    // $order = carry_
}