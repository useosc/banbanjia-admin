<?php

// use function Qiniu\json_decode;

defined("IN_IA") or exit("Access Denied");
if (!function_exists("member_wxapp2openid")) {
    function member_wxapp2openid($openid_wxapp = "")
    {
        global $_W;
        if (empty($openid_wxapp)) {
            $openid_wxapp = $_W["openid_wxapp"];
        }
        $openid = pdo_fetchcolumn("select openid from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and openid_wxapp = :openid_wxapp", array(":uniacid" => $_W["uniacid"], ":openid_wxapp" => $openid_wxapp));
        return $openid;
    }
}

function icheckauth($force = true) //鉴权
{
    global $_W;
    global $_GPC;
    load()->model("mc");
    $_W["member"] = array();
    // if (is_weixin() && !defined('IN_WXAPP')) { //公众号
    //     if (!empty($_W['openid'])) {
    //         if (empty($force)) {
    //             $member = get_member($_W['openid']);
    //         } else {
    //             $fansInfo = mc_oauth_userinfo();
    //         }
    //     }
    // } else {
    if (defined("IN_WXAPP")) { //小程序
        if (!empty($_W['openid'])) {
            //统一用户
            if (!empty($_W['unionid'])) {
                pdo_update('hello_banbanjia_members', array('openid_wxapp' => $_W['openid']), array('uniacid' => $_W['uniacid'], 'unionid' => $_W['unionid']));
                pdo_update('hello_banbanjia_members', array('unionId' => $_W['unionid']), array('uniacid' => $_W['uniacid'], 'openid_wxapp' => $_W['openid']));
                $member = get_member($_W['unionid'], 'unionid');
            }
            if (empty($member)) {
                $member = get_member($_W["openid"], "openid_wxapp");
            }
            if (!empty($member)) {
                $_W['member'] = $member;
                $update = array();
                if (empty($member['openid_wxapp'])) {
                    $update['openid_wxapp'] = $_W['openid'];
                }
                if (empty($member['unionId']) && !empty($_W['unionid'])) {
                    $update['unionId'] = $_W['unionid'];
                }
                if (!empty($update)) {
                    pdo_update('hello_banbanjia_members', $update, array('id' => $_W['member']['id']));
                }
            }
        }
    } else {
        if (defined("IN_WAP")) { //h5
            // $key = "we7_hello_banbanjia_member_session_" . $_W['uniacid'];
            // if (isset($_GPC[$key])) {
            //     $session = json_decode(base64_decode($_GPC[$key]), true);
            //     if (is_array($session)) {
            //         $member = get_member($session["uid"]);
            //         if (is_array($member) && $session["hash"] == $member["password"]) {
            //             $_W["member"] = $member;
            //         } else {
            //             isetcookie($key, false, -100);
            //         }
            //     } else {
            //         isetcookie($key, false, -100);
            //     }
            // }
            $member = get_member($_SESSION["member"]['uid']);
            $_W['member'] = $member;
        }
    }
    // }
    // $_W['member'] = array(
    //     'uid' => 100,
    //     'status' => 1,
    // );
    if ($_W['member']['uid'] > 0) {
        $_W['openid'] = $_W['openid_wechat'] = $_W['member']['openid'];
        $_W['openid_wxapp'] = $_W['member']['openid_wxapp'];
        if (defined('IN_WXAPP')) {
            $_W['openid'] = $_W['member']['openid'] = $_W['openid_wxapp'];
        }
        member_group_update();
        $_W['member']['is_store_newmember'] = 1;
        $_W['member']['is_mall_newmember'] = 1;
        $config_newmember_condition = 0;
        if (!empty($_W['we7_hello_banbanjia']['config']['activity'])) {
            $config_newmember_condition = $_W['we7_hello_banbanjia']['config']['activity']['newmember']['newmember_condition'];
        }
        if ($_GPC['sid'] > 0) { //店铺新客
            if ($config_newmember_condition == 1) {
                $is_exist = pdo_fetch('select id from ' . tablename('hello_banbanjia_carry_order') . ' where uniacid = :uniacid and sid = :sid and uid = :uid and status != 6', array(':uniacid' => $_W['uniacid'], ':sid' => intval($_GPC['sid']), ':uid' => $_W['member']['uid']));
            } else {
                $is_exist = pdo_get('hello_banbanjia_carry_order', array('uniacid' => $_W['uniacid'], 'sid' => intval($_GPC['sid']), 'uid' => $_W['member']['uid']), array('id'));
            }
            if (!empty($is_exist)) {
                $_W['member']['is_store_newmember'] = 0;
                $_W['member']['is_mall_newmember'] = 0;
            }
        }
        if ($_W['member']['is_mall_newmember'] == 1) { //平台新客
            if ($config_newmember_condition == 1) {
                $is_exist = pdo_fetch('select id from ' . tablename('hello_banbanjia_carry_order') . ' where uniacid = :uniacid and uid = :uid and status != 6', array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid']));
            } else {
                $is_exist = pdo_get('hello_banbanjia_carry_order', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']), array('id'));
            }
            if (!empty($is_exist)) {
                $_W['member']['is_mall_newmember'] = 0;
            }
        }
        if (!$_W['member']['status'] && $force) {
            imessage('您暂时无权访问平台', 'close', 'info');
        }
        return true;
    }
    if ($force) {
        $forward = base64_encode($_SERVER['QUERY_STRING']);
        if (defined('IN_WXAPP')) {
            imessage(error(41009, '请先登录'), '', 'ajax');
        } else {
            if (defined('IN_WAP')) {
                $result = array("errno" => 41009, "message" => "请先登录", "sessionid" => $_W["session_id"], true);
                imessage($result, "", "ajax");
            }
        }
        exit;
    }
}
//获取用户信息
function get_member($openid, $field = 'openid')
{
    global $_W;
    $uid = intval($openid);
    $fields = array("id", "uid", "groupid", "groupid_updatetime", "openid", "openid_wxapp", "token", "credit1", "credit2", "avatar", "nickname", "sex", "realname", "mobile", "password", "mobile_audit", "setmeal_id", "setmeal_day_free_limit", "setmeal_deliveryfee_free_limit", "setmeal_starttime", "setmeal_endtime", "is_sys", "status", "addtime", "spread1", "spread2", "spreadcredit2", "spreadfixed", "svip_status", "svip_starttime", "svip_endtime", "svip_credit1", "account","continue_sign_day","last_sign_time","sign_total_day");
    if ($uid == 0) {
        $info = pdo_get('hello_banbanjia_members', array('uniacid' => $_W['uniacid'], $field => $openid), $fields);
    } else {
        $info = pdo_get("hello_banbanjia_members", array("uniacid" => $_W["uniacid"], "uid" => $openid), $fields);
    }
    if (!empty($info)) {
        $groups = member_groups();
        $info["groupname"] = $groups[$info["groupid"]]["title"];
        $info["svip_credit1"] = floatval($info["svip_credit1"]);
        $info["account"] = iunserializer($info["account"]);
        $update = array();
        if (empty($info['token'])) {
            $info['token'] = random(32);
            pdo_update('hello_banbanjia_members', array('token' => $info['token']), array('id' => $info['id']));
        }
        if ($info["svip_status"] == 1 && $info["svip_endtime"] <= TIMESTAMP) { //超级会员
            $update["svip_status"] = 2;
            $info["svip_status"] = $update["svip_status"];
        }
        if (!empty($update)) {
            pdo_update("hello_banbanjia_members", $update, array("id" => $info["id"]));
        }
        $openid = $info["openid"];
        $info["svip_credit1"] = floatval($info["svip_credit1"]);
        $info["account"] = iunserializer($info["account"]);
        $update = array();
        if (empty($info['token'])) {
            $update['token'] = random(32);
            $info['token'] = $update['token'];
        }
        if ($info["svip_status"] == 1 && $info["svip_endtime"] <= TIMESTAMP) { //超级会员过期
            $update["svip_status"] = 2;
            $info["svip_status"] = $update["svip_status"];
        }
        if (!empty($update)) {
            pdo_update('hello_banbanjia_members', $update, array('id' => $info['id']));
        }
        $openid = $info['openid'];
        return $info;
    }
}
//用户等级
function member_groups()
{
    global $_W;
    $config_member = $_W['we7_hello_banbanjia']['config']['member'];
    return $config_member['group'];
}
//更新用户等级
function member_group_update($wx_tpl = false)
{
    global $_W;
    if ($_W['member']['groupid_updatetime'] > TIMESTAMP - 600) {
        return true;
    }
    $condition = ' where uniacid = :uniacid and is_pay = 1 and uid = :uid';
    $params = array(
        ':uniacid' => $_W['uniacid'],
        ':uid' => $_W['member']['uid'],
    );
    $config_member = $_W['we7_hello_banbanjia']['config']['member'];
    if ($config_member['group_update_mode'] == 'order_money') {
        //搬运订单消费总额满
        $condition .= " and status = 5";
        $result = pdo_fetchcolumn('select sum(final_fee) from' . tablename('hello_banbanjia__carry_order') . $condition, $params);
        $result = round($result, 2);
    } elseif ($config_member['group_update_mode'] == 'order_count') {
        //搬运订单消费次数满
        $condition .= " and status = 5";
        $result = pdo_fetchcolumn('select count(*) from' . tablename('hello_banbanjia__carry_order') . $condition, $params);
        $result = intval($result);
    }
    $old_group_id = $_W['member']['groupid'];
    $groups = member_groups();
    foreach ($groups as $group) {
        if (($result >= $group['group_condition']) && ($group['group_condition'] > $groups[$old_group_id]['group_condition'])) {
            $group_id = $group['id'];
        }
    }
    pdo_update('hello_banbanjia_members', array('groupid' => $group_id, 'groupid_updatetime' => TIMESTAMP), array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
    if ($wx_tpl) {
        //微信模板消息
    }
    $_W['member']['groupid'] = $group_id;
    $_W['member']['groupname'] = $groups[$group_id]['title'];
    return true;
}
//用户账变
function member_credit_update($uid, $credittype, $creditval = 0, $log = array(), $wxtpl_notice = true)
{
    global $_W;
    $fields = array("id", "uid", "groupid", "groupid_updatetime", "uid_majia", "uid_qianfan", "openid", "token", "credit1", "credit2", "avatar", "nickname", "sex", "realname", "mobile", "password", "mobile_audit", "setmeal_id", "setmeal_day_free_limit", "setmeal_deliveryfee_free_limit", "setmeal_starttime", "setmeal_endtime", "is_sys", "status", "addtime", "spread1", "spread2");
    $member = pdo_get("hello_banbanjia_members", array("uniacid" => $_W["uniacid"], "uid" => $uid));
    if (empty($member)) {
        return error(-1, "会员不存在");
    }
    if (!in_array($credittype, array("credit1", "credit2"))) {
        return error("-1", "积分类型有误");
    }
    $credittype = trim($credittype);
    $creditval = floatval($creditval);
    if (empty($creditval)) {
        return true;
    }
    if ($member["is_sys"] == 1) {
        load()->model("mc");
        $result = mc_credit_update($uid, $credittype, $creditval, $log);
    } else {
        $value = $member[$credittype];
        if (0 < $creditval || 0 <= $value + $creditval) {
            pdo_update("hello_banbanjia_members", array($credittype => $value + $creditval), array("uid" => $uid));
            $result = true;
        } else {
            return error("-1", "积分类型为" . $credittype . "的积分不够，无法操作。");
        }
    }
    if (!empty($wxtpl_notice)) {
        load()->func("communication");
        $openid = member_uid2openid($uid);
        if (empty($openid)) {
            return true;
        }
        $member = get_member($uid);
        $config = $_W["we7_hello_banbanjia"]["config"];
        if ($credittype == "credit1") {
            $params = array("first" => "您在" . $config["mall"]["title"] . "的账户积分有新的变动", "keyword1" => "积分变动", "keyword2" => (string) $creditval . "积分", "keyword3" => date("Y-m-d H:i", TIMESTAMP), "keyword4" => 0 < $creditval ? "积分充值" : "积分消费", "remark" => implode("\n", array("积分余额:" . $member["credit1"], "备注:" . $log[1])));
        } else {
            $params = array("first" => "您在" . $config["mall"]["title"] . "的账户余额有新的变动", "keyword1" => "余额变动", "keyword2" => (string) $creditval . "余额", "keyword3" => date("Y-m-d H:i", TIMESTAMP), "keyword4" => 0 < $creditval ? "余额充值" : "余额消费", "remark" => implode("\n", array("账户余额:" . $member["credit2"], "备注:" . $log[1])));
        }
        $send = sys_wechat_tpl_format($params);
        $acc = TyAccount::create($_W["acid"]);
        $url = ivurl("pages/member/mine", array(), true);
        $miniprogram = "";
        if (MODULE_FAMILY == "wxapp") {
            $miniprogram = array("appid" => $_W["we7_hello_banbanjia"]["config"]["wxapp"]["basic"]["key"], "pagepath" => "pages/member/mine");
        }
        if (!is_error($acc)) {
            $status = $acc->sendTplNotice($openid, $_W["we7_hello_banbanjia"]["config"]["notice"]["wechat"]["account_change_tpl"], $send, $url, $miniprogram);
            if (is_error($status)) {
                slog("wxtplNotice", "平台账户变动微信通知会员", $send, $status["message"]);
            }
        }
    }
    return $result;
}
//uid2openid
function member_uid2openid($uid = 0)
{
    global $_W;
    if (empty($uid)) {
        $uid = $_W["member"]["uid"];
    }
    $openid = pdo_fetchcolumn("select openid from " . tablename("hello_banbanjia_members") . " where uid = :uid", array(":uid" => $uid));
    return $openid;
}
//绑定手机号
function bind_phone($phone)
{
    global $_W;
    $member_uid = $_W['fans']['uid'];
    pdo_update("hello_banbanjia_members", array("mobile" => $phone, "mobile_audit" => 1), array("uid" => $member_uid, "uniacid" => $_W["uniacid"]));
    return true;
}
//合并用户信息
function member_union($unionId, $field = 'unionId')
{
    global $_W;
    if (empty($unionId)) {
        return false;
    }
    $fields = array("id", "uid", "groupid", "groupid_updatetime", "openid", "openid_wxapp", "token", "credit1", "credit2", "avatar", "nickname", "sex", "realname", "mobile", "password", "mobile_audit", "setmeal_id", "setmeal_day_free_limit", "setmeal_deliveryfee_free_limit", "setmeal_starttime", "setmeal_endtime", "is_sys", "status", "addtime", "spread1", "spread2", "spreadcredit2", "spreadfixed");
    $fields_str = implode(',', $fields);
    $members = pdo_fetchall("select" . $fields_str . " from " . tablename('hello_banbanjia_members') . " where uniacid = :uniacid and " . $field . " = :unionId order by id asc", array(":uniacid" => $_W['uniacid'], ":unionId" => $unionId));
    if (empty($members) || count($members) == 1) {
        return false;
    }
    $update = array();
    $uids = $ids = array();
    $setmeals = array();
    foreach ($members as $member) {
        $ids[] = $member['id'];
        $uids[] = $member['uid'];
        if (0 < $member["setmeal_endtime"]) {
            $setmeals[$member["uid"]] = $member["setmeal_endtime"];
        }
    }
    return true;
}
//顾客数据统计
function member_plateform_amount_stat()
{
    global $_W;
    $stat = array();
    $today_starttime = strtotime(date('Y-m-d'));
    $yesterday_starttime = $today_starttime - 86400;
    $month_starttime = strtotime(date('Y-m'));
    $stat['yesterday_num'] = intval(pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and addtime >= :starttime and addtime <= :endtime", array(":uniacid" => $_W['uniacid'], ":starttime" => $yesterday_starttime, ":endtime" => $today_starttime)));
    $stat["today_num"] = intval(pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and addtime >= :starttime", array(":uniacid" => $_W["uniacid"], ":starttime" => $today_starttime)));
    $stat["month_num"] = intval(pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and addtime >= :starttime", array(":uniacid" => $_W["uniacid"], ":starttime" => $month_starttime)));
    $stat["total_num"] = intval(pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid", array(":uniacid" => $_W["uniacid"])));
    return $stat;
}
//
function member_amount_stat($id)
{
    global $_W;
    $stat = array();
    $today_starttime = strtotime(date('Y-m-d'));
    $yesterday_starttime = $today_starttime - 86400;
    $month_starttime = strtotime(date('Y-m'));
    $stat['yesterday_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_store_members') . ' where uniacid = :uniacid and sid = :sid and success_first_time >= :starttime and success_first_time <= :endtime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $yesterday_starttime, ':endtime' => $today_starttime)));
    $stat['today_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_store_members') . ' where uniacid = :uniacid and sid = :sid and success_first_time >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $today_starttime)));
    $stat['month_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_store_members') . ' where uniacid = :uniacid and sid = :sid and success_first_time >= :starttime', array(':uniacid' => $_W['uniacid'], ':sid' => $sid, ':starttime' => $month_starttime)));
    $stat['total_num'] = intval(pdo_fetchcolumn('select count(*) from ' . tablename('hello_banbanjia_store_members') . ' where uniacid = :uniacid and sid = :sid', array(':uniacid' => $_W['uniacid'], ':sid' => $sid)));
    return $stat;
}

function member_fetch($uid = 0)
{
    global $_W;
    if (!$uid) {
        $uid = $_W['member']['uid'];
    }
    $member = pdo_get("hello_banbanjia_members", array("uniacid" => $_W['uniacid'], "uid" => $uid));
    if(!empty($member)){
        $member["search_data"] = iunserializer($member["search_data"]);
        if (!is_array($member["search_data"])) {
            $member["search_data"] = array();
        }
    }
    return $member;
}
