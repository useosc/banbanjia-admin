<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lclass("wxapp");
load()->model("mc");
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'openid';
$account_api = new Wxapp();
if ($ta == 'openid') {
    $code = $_GPC['code'];
    if (empty($code)) {
        imessage(error(-1, '通信错误，请在微信中重新发起请求'), '', 'ajax');
    }
    $oauth = $account_api->getOauthInfo($code);
    if (!empty($oauth) && !is_error($oauth)) {
        $_SESSION['openid'] = $oauth['openid'];
        $_SESSION['openid_wxapp'] = $oauth['openid'];
        $_SESSION['unionid'] = $oauth['unionid'];
        $_SESSION['session_key'] = $oauth['session_key'];
        $fans = mc_fansinfo($oauth['openid']);
        if (empty($fans)) {
            $record = array("openid" => $oauth["openid"], "unionid" => $oauth["unionid"], "uid" => 0, "acid" => $_W["acid"], "uniacid" => $_W["uniacid"], "salt" => random(8), "updatetime" => TIMESTAMP, "nickname" => "", "follow" => "1", "followtime" => TIMESTAMP, "unfollowtime" => 0, "tag" => "");
            $union_fans = array();
            if (!empty($oauth["unionid"])) {
                $union_fans = pdo_get("mc_mapping_fans", array("uniacid" => $_W["uniacid"], "unionid" => $oauth["unionid"], "openid !=" => $oauth["openid"]));
            }
            if (empty($union_fans)) {
                $email = md5($oauth["openid"]) . "@we7.cc";
                $email_exists_member = pdo_fetchcolumn("SELECT uid FROM " . tablename("mc_members") . " WHERE uniacid = :uniacid AND email = :email", array(":uniacid" => $_W["uniacid"], ":email" => $email));
                if (!empty($email_exists_member)) {
                    $uid = $email_exists_member;
                } else {
                    $default_groupid = pdo_fetchcolumn("SELECT groupid FROM " . tablename("mc_groups") . " WHERE uniacid = :uniacid AND isdefault = 1", array(":uniacid" => $_W["uniacid"]));
                    $data = array("uniacid" => $_W["uniacid"], "email" => $email, "salt" => random(8), "groupid" => $default_groupid, "createtime" => TIMESTAMP, "password" => md5($message["from"] . $data["salt"] . $_W["config"]["setting"]["authkey"]), "nickname" => "", "avatar" => "", "gender" => "", "nationality" => "", "resideprovince" => "", "residecity" => "");
                    pdo_insert("mc_members", $data);
                    $uid = pdo_insertid();
                }
            } else {
                $uid = $union_fans["uid"];
            }
            $fans["uid"] = $uid;
            $record["uid"] = $fans["uid"];
            $fans["uid"] = $record["uid"];
            $_SESSION["uid"] = $uid;
            pdo_insert("mc_mapping_fans", $record);
        }
        if (!empty($oauth["unionid"])) {
            $mall_member = get_member($oauth["unionid"], "unionId");
        }
        if (empty($wmall_member)) {
            $mall_member = get_member($oauth["openid"], "openid_wxapp");
        }
        if (empty($mall_member)) { //如果为空则新建用户
            $mall_member = array("uniacid" => $_W["uniacid"], "uid" => $fans["uid"] ? $fans["uid"] : date("His") . random(3, true), "openid_wxapp" => $oauth["openid"], "unionId" => $oauth["unionid"], "is_sys" => 1, "status" => 1, "token" => random(32), "addtime" => TIMESTAMP);
            pdo_insert("hello_banbanjia_members", $mall_member);
        } else {
            $update = array("openid_wxapp" => $oauth["openid"]);
            if (empty($wmall_member["uid"])) {
                $update["uid"] = $fans["uid"];
            }
            if (!empty($oauth["unionid"])) {
                $update["unionId"] = $oauth["unionid"];
                pdo_update("hello_banbanjia_members", $update, array("uniacid" => $_W["uniacid"], "unionId" => $oauth["unionid"]));
            }
            pdo_update("hello_banbanjia_members", $update, array("uniacid" => $_W["uniacid"], "openid_wxapp" => $oauth["openid"]));
        }
        $member = get_member($oauth['openid'], 'openid_wxapp');
        unset($member['password']);
        unset($member['salt']);
        $sessionid = $_W["session_id"];
        if ($_GPC["istate"]) {
            $sessionid = $member["openid_wxapp"];
        }
        $account_api->result(0, '', array('sessionid' => $sessionid, 'member' => $member));
    } else {
        $account_api->result(2000, $oauth['message']);
    }
} else {
    if ($ta == 'userinfo') {
        $encrypt_data = $_GPC['encryptedData'];
        $iv = $_GPC['iv'];
        if (empty($_SESSION["session_key"]) || empty($encrypt_data) || empty($iv)) {
            $account_api->result(2001, "请先登录");
        }
        $sign1 = sha1(htmlspecialchars_decode($_GPC["rawData"], ENT_QUOTES) . $_SESSION["session_key"]);
        $sign2 = sha1($_POST["rawData"] . $_SESSION["session_key"]);
        if ($sign1 !== $_GPC["signature"] && $sign2 !== $_GPC["signature"]) {
            $account_api->result(2010, "签名错误");
        }
        $userinfo = $account_api->pkcs7Encode($encrypt_data, $iv);
        if (is_error($userinfo)) {
            $account_api->result(2002, "解密出错");
        }
    }
}
