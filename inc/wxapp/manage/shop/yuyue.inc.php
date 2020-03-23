<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list'){
    $result = pdo_getall("hello_banbanjia_store_yuyue",array("sid"=>$sid,'uniacid'=>$_W['uniacid']));
    if(!empty($result)){
        foreach($result as &$item){
            $item['addtime_cn'] = date("Y-m-d H:i:s",$item['addtime']);
        }
    }
    imessage(error(0,$result),'','ajax');
}