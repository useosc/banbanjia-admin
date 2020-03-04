<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
icheckauth(true);
if ($op == 'index') {
    $informations = ask_get_informations();
    imessage(error(0, $informations), '', 'ajax');
}

if ($op == 'detail') {
    $id = intval($_GPC['id']);
    //浏览记录
    $footmark = pdo_get("hello_banbanjia_member_footmark", array("uniacid" => $_W['uniacid'], 'uid' => $_W['member']['uid'], "cid" => $id, 'type' => 'ask', 'stat_day' => date("Ymd")), array("id"));
    if (empty($footmark)) {
        $insert = array("uniacid" => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'cid' => $id, 'type' => 'ask', 'addtime' => TIMESTAMP, 'stat_day' => date("Ymd"));
        pdo_insert("hello_banbanjia_member_footmark", $insert);
    }

    gohome_update_activity_flow("ask",$id,"looknum");
    $information = ask_get_information($id);
    $answers = ask_get_answers($id);
    $result = array("detail" => $information,"answers" => $answers);
    imessage(error(0,$result),'','ajax');
}

if($op == 'answer') {
    $id = intval($_GPC['id']);
    $update = array("uniacid" => $_W['uniacid'],'agentid' => $_W['agentid'],'aid' => $id,'content' => trim($_GPC['content']),'uid'=> $_W['member']['uid'],'nickname' => $_W['member']['nickname'],'avatar' => $_W['member']['avatar'],'addtime' => TIMESTAMP);
    pdo_insert("hello_banbanjia_ask_answer",$update);
    gohome_update_activity_flow("ask",$id,"answernum");
    imessage(error(0,'回答成功'),'','ajax');
}