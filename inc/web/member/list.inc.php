<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
ini_set("display_errors", "1"); //显示出错信息
error_reporting(E_ALL ^ E_NOTICE);
mload()->lmodel('member');
$_W["page"]["title"] = "顾客列表";
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";

if ($op == 'del') {
    $id = intval($_GPC['id']);
    $uid = intval($_GPC['uid']);
    if (empty($id) || empty($uid)) {
        imessage(error(0, '顾客不存在'), referer(), 'ajax');
    }
    pdo_delete('hello_banbanjia_members', array('uniacid' => $_W['uniacid'], 'id' => $id));
    mlog(6001, $uid);
    imessage(error(0, '删除成功'), referer(), 'ajax');
}
if ($op == "list") {
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $key = trim($_GPC['key']);
    if (!empty($key)) {
        $time = strtotime("-30 days");
        if ($key == "success_30") {
            $condition .= " and success_last_time >= :time";
        }
        $params[":time"] = $time;
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_members") . $condition, $params);
    $data = pdo_fetchall("select * from " . tablename("hello_banbanjia_members") . $condition . " LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
}
if ($op == 'info') {
    $uid = intval($_GPC['uid']);
    $member = pdo_get('hello_banbanjia_members', array('uniacid' => $_W['uniacid'], 'uid' => $uid));
    if (empty($member)) {
        imessage(error(-1, '顾客不存在或已被删除'), referer(), 'error');
    }
}
if($op == 'changes'){
    $uid = intval($_GPC['uid']);
    $member = get_member($uid);
    if(empty($member)){
        imessage(error(-1, "会员不存在或已经删除"), referer(), "ajax");
    }
    if($_W['ispost']){
        $type = trim($_GPC['type']);
        $change_type = intval($_GPC["change_type"]);
        $amount = floatval($_GPC["amount"]);
        $remark = trim($_GPC["remark"]);
        $credit = $member["credit1"];
        $credit_text = "积分";
        if ($type == "credit2") {
            $credit = $member["credit2"];
            $credit_text = "余额";
        }
        if ($type == "svip_credit1") {
            $credit = $member["svip_credit1"];
            $credit_text = "奖励金";
        }
        if ($change_type == 1) {
            $amount = "+" . $amount;
        } else {
            if ($change_type == 2) {
                $amount = "-" . $amount;
                if ($credit - $amount < 0) {
                    $amount = "-" . $credit;
                }
            } else {
                if ($change_type == 3) {
                    $amount = $amount - $credit;
                }
            }
        }
        $log = array($member["uid"], $remark);
        if ($type == "svip_credit1") {
            mload()->lmodel("plugin");
            pload()->model("svip");
            $result = svip_member_svip_credit1_update($uid, $amount, "平台修改", $remark);
        } else {
            $result = member_credit_update($member["uid"], $type, $amount, $log);
        }
        if (is_error($result)) {
            mlog(6002, $member["uid"], "变更失败!" . $result["message"] . "。变动类型：" . $credit_text . "。变动方式:" . $change_type . "，金额：" . $amount . "。备注:" . $remark);
        } else {
            mlog(6002, $member["uid"], "变更成功。变动类型：" . $credit_text . "。变动方式:" . $change_type . "，金额：" . $amount . "。备注:" . $remark);
        }
        imessage(error(0, (string) $credit_text . "变动成功"), referer(), "ajax");
    }
    include itemplate('member/op');
    exit;
}
include itemplate("member/list");
