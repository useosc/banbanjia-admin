<?php
defined("IN_IA") or exit("Access Denied");

function icheckauth($force = true) //鉴权
{
    global $_W;
    global $_GPC;
    load()->model("mc");

    $_W["member"] = array();
    if (is_weixin() && !defined('IN_WXAPP')) {
        if (!empty($_W['openid'])) {
            if(empty($force)){
                $member = get_member($_W['openid']);
            }else{
                $fansInfo = mc_oauth_userinfo();
            }
         }
    } else {
        if (defined("IN_WXAPP")) {
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
        }
    }

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
        $_W['member']['is_store_newmember'] = 1;
        $_W['member']['is_mall_newmember'] = 1;
        $config_newmember_condition = 0;

        if (!$_W['member']['status'] && $force) {
            imessage('您暂时无权访问平台', 'close', 'info');
        }
        return true;
    }
    if ($force) {
        $forward = base64_encode($_SERVER['QUERY_STRING']);
        if (defined('IN_WXAPP')) {
            imessage(error(41009, '请先登录'), '', 'ajax');
        }
        exit;
    }
}
//获取用户信息
function get_member($openid, $field = 'openid')
{
    global $_W;
    $uid = intval($openid);
    $fields = array("id", "uid", "groupid", "groupid_updatetime", "openid", "openid_wxapp", "token", "credit1", "credit2", "avatar", "nickname", "sex", "realname", "mobile", "password", "mobile_audit", "setmeal_id", "setmeal_day_free_limit", "setmeal_deliveryfee_free_limit", "setmeal_starttime", "setmeal_endtime", "is_sys", "status", "addtime", "spread1", "spread2", "spreadcredit2", "spreadfixed", "svip_status", "svip_starttime", "svip_endtime", "svip_credit1", "account");
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
function member_groups()
{
    global $_W;
    $config_member = $_W['we7_hello_banbanjia']['config']['member'];
    return $config_member['group'];
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
