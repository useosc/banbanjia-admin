<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list') {
    $traces = pdo_getall("hello_banbanjia_store_trace",array("uniacid" => $_W['uniacid'],"sid" => $_W['sid']));
    if(!empty($traces)){
        foreach($traces as &$trace) {
            $trace['addtime_cn'] = date("Y-m-d H:i:s",$trace['addtime']);
            $trace['modifytime_cn'] = date("Y-m-d H:i:s",$trace['modifytime']);
            $trace['nexttime_cn'] = date("Y-m-d H:i:s",$trace['nexttime']);
        }
    }
    imessage(error(0,$traces),'','ajax');
}
if($ta == 'post') {
    error_reporting(E_ERROR);
    $_W["page"]["title"] = "新增跟单";

    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {

        $_GPC["cusid"] = floatval($_GPC["cusid"]) ? floatval($_GPC["cusid"]) : imessage(error(-1, "客户不能为空"), "", "ajax");

        $data = array("uniacid" => $_W["uniacid"], "sid" => $_W['sid'],  "cusid" => $_GPC['cusid'],"cusname" => trim($_GPC['cusname']),
        "nexttime" => intval($_GPC['nexttime'])/1000, "type" => trim($_GPC['type']), "state" => trim($_GPC['state']),
        "create_clerk_id" => $_W['manager']['id'],"create_clerk_name" => $_W['manage']['realname'], 
        "content" => trim($_GPC['content']),"modifytime" => TIMESTAMP,
       "isdelete" => 0);
        if (!$id) {
            $data["addtime"] = TIMESTAMP;
            pdo_insert("hello_banbanjia_store_trace", $data);
            imessage(error(0, "新增跟单成功"), "", "ajax");
        } else {
            pdo_update("hello_banbanjia_store_trace", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑跟单成功"), "", "ajax");
    }


    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_store_trace", array("uniacid" => $_W["uniacid"], "id" => $id));
        imessage(error(0, $item), "", "ajax");
    }
}