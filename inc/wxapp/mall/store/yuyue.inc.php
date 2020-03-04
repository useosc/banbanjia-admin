<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth(false);
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if ($ta == 'index') {
    $data = array(
        "uniacid" => $_W['uniacid'], "sid" => trim($_GPC['sid']), 'uid' => $_W['member']['uid'],
        'contact' => trim($_GPC['contact']), 'mobile' => trim($_GPC['mobile']),'addtime' => TIMESTAMP
    );
    pdo_insert("hello_banbanjia_store_yuyue",$data);
    $yuyue_id = pdo_insertid();
    imessage(error(0,$yuyue_id),'','ajax');
}
if($ta == 'list') {
    // error_reporting(E_ALL);
    $data = pdo_fetchall("SELECT y.* ,s.title,s.logo FROM " . tablename("hello_banbanjia_store_yuyue") . " as y left join " . tablename("hello_banbanjia_store") ." as s on y.sid = s.id where y.uniacid = :uniacid and y.uid = :uid",array(":uniacid" => $_W['uniacid'],':uid'=>$_W['member']['uid']));
    if(!empty($data)){
        foreach($data as &$item){
            $item['logo'] = tomedia($item['logo']);
            $item['addtime_cn'] = date("Y-m-d H:i:s",$item['addtime']);
        }
    }
    imessage(error(0,$data),'','ajax');
}