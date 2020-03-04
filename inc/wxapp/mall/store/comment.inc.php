<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth(false);
mload()->lmodel('store');
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if ($ta == 'list') { 
    // error_reporting(E_ALL);
    $comments = store_get_comments();
    imessage(error(0, $comments), '', 'ajax');
}

if ($ta == 'save') {
    $sid = intval($_GPC['sid']);
    if (empty($sid)) {
        imessage(error(1, '没有相关商家信息', '', 'ajax'));
    }
    $insert = array(
        "uniacid" => $_W['uniacid'], 'sid' => $sid, 'uid' => $_W['member']['uid'],
        'score1' => intval($_GPC['score1']), 'score2' => intval($_GPC['score2']), 'score3' => intval($_GPC['score3']), 'score4' => intval($_GPC['score4']), 'score5' => intval($_GPC['score5']),
        'content' => trim($_GPC['content']), 'clientip' => CLIENT_IP, 'addtime' => TIMESTAMP
    );
    pdo_insert("hello_banbanjia_store_comment",$insert);
    imessage(error(0,'评价成功'),'','ajax');
}