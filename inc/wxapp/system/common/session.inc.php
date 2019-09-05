<?php
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lclass("wxapp");
load()->model("mc");
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'openid';
$account_api = new Wxapp();
if ($_GPC['from'] == 'wxapp') {
    if ($ta == 'openid') { //获取openid (新建用户)
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
            if (empty($mall_member)) {
                $mall_member = get_member($oauth["openid"], "openid_wxapp");
            }
            if (empty($mall_member)) { //如果为空则新建用户
                $mall_member = array("uniacid" => $_W["uniacid"], "uid" => $fans["uid"] ? $fans["uid"] : date("His") . random(3, true), "openid_wxapp" => $oauth["openid"], "unionId" => $oauth["unionid"], "is_sys" => 1, "status" => 1, "token" => random(32), "addtime" => TIMESTAMP);
                pdo_insert("hello_banbanjia_members", $mall_member);
            } else {
                $update = array("openid_wxapp" => $oauth["openid"]);
                if (empty($mall_member["uid"])) {
                    $update["uid"] = $fans["uid"];
                }
                if (!empty($oauth["unionid"])) {
                    $update["unionId"] = $oauth["unionid"];
                    pdo_update("hello_banbanjia_members", $update, array("uniacid" => $_W["uniacid"], "unionId" => $oauth["unionid"]));
                }
                pdo_update("hello_banbanjia_members", $update, array("uniacid" => $_W["uniacid"], "openid_wxapp" => $oauth["openid"]));
            }
            $member = get_member($oauth['openid'], 'openid_wxapp');
            $_SESSION["member"] = $member;
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
        if ($ta == 'userinfo') { //保存微信用户信息
            $encrypt_data = $_GPC['encryptedData'];
            $iv = $_GPC['iv'];
            if (empty($_SESSION["session_key"]) || empty($encrypt_data) || empty($iv)) {
                $account_api->result(2001, "请先登录");
            }
            $sign1 = sha1(htmlspecialchars_decode($_GPC["rawData"], ENT_QUOTES) . $_SESSION["session_key"]);
            $sign2 = sha1($_GPC["rawData"] . $_SESSION["session_key"]);
            if ($sign1 !== $_GPC["signature"] && $sign2 !== $_GPC["signature"]) {
                $account_api->result(2010, "签名错误");
            }
            $userinfo = $account_api->pkcs7Encode($encrypt_data, $iv);
            if (is_error($userinfo)) {
                $account_api->result(2002, "解密出错");
            }
            $fans = mc_fansinfo($userinfo["openId"]);
            $fans_update = array("nickname" => $userinfo["nickName"], "unionid" => $userinfo["unionId"], "tag" => base64_encode(iserializer(array("subscribe" => 1, "openid" => $userinfo["openId"], "nickname" => $userinfo["nickName"], "sex" => $userinfo["gender"], "language" => $userinfo["language"], "city" => $userinfo["city"], "province" => $userinfo["province"], "country" => $userinfo["country"], "headimgurl" => $userinfo["avatarUrl"]))));
            pdo_update("mc_mapping_fans", $fans_update, array("fanid" => $fans["fanid"]));
            if (!empty($userinfo["unionId"])) {
                $mall_member = get_member($userinfo["unionId"], "unionId");
            }
            if (empty($mall_member)) {
                $mall_member = get_member($userinfo["openId"], "openid_wxapp");
            }
            if (empty($mall_member)) {
                $mall_member = array("uniacid" => $_W["uniacid"], "uid" => $fans["uid"] ? $fans["uid"] : date("His") . random(3, true), "openid_wxapp" => $userinfo["openId"], "unionId" => $userinfo["unionId"], "nickname" => $userinfo["nickName"], "realname" => "", "mobile" => "", "sex" => $userinfo["gender"] == 1 ? "男" : "女", "avatar" => rtrim(rtrim($userinfo["avatarUrl"], "0"), 132) . 132, "is_sys" => 1, "status" => 1, "token" => random(32), "addtime" => TIMESTAMP);
                pdo_insert("hello_banbanjia_members", $mall_member);
            } else {
                $update = array("openid_wxapp" => $userinfo["openId"], "nickname" => $userinfo["nickName"], "sex" => $userinfo["gender"] == 1 ? "男" : "女", "avatar" => rtrim(rtrim($userinfo["avatarUrl"], "0"), 132) . 132);
                if (empty($mall_member["uid"])) {
                    $update["uid"] = $fans["uid"];
                }
                if (!empty($userinfo["unionId"])) {
                    $update["unionId"] = $userinfo["unionId"];
                    pdo_update("hello_banbanjia_members", $update, array("uniacid" => $_W["uniacid"], "unionId" => $userinfo["unionId"]));
                }
                pdo_update("hello_banbanjia_members", $update, array("uniacid" => $_W["uniacid"], "openid_wxapp" => $userinfo["openId"]));
            }
            $member = get_member($userinfo["openId"], "openid_wxapp");
            unset($member["password"]);
            unset($member["salt"]);
            $account_api->result(0, "", $member);
        } else if ($ta == 'wxbind') { //绑定微信手机号
            $encrypt_data = $_GPC['encryptedData'];
            $iv = $_GPC['iv'];
            if (empty($_SESSION["session_key"]) || empty($encrypt_data) || empty($iv)) {
                $account_api->result(2001, "请先登录");
            }
            $phoneinfo = $account_api->pkcs7Encode($encrypt_data, $iv);
            //保存手机号
            if (!bind_phone($phoneinfo['phoneNumber'])) {
                $account_api->result(1, "绑定手机号错误");
            }
            if (is_error($phoneinfo)) {
                $account_api->result(2002, "解密出错");
            } else {
                $account_api->result(0, $phoneinfo);
            }
        } else if ($ta == 'phonebind') { //wx绑定手机号（验证码）
            $user = $_W["member"];
            $id = $user["id"];
            $mobile = trim($_GPC["mobile"]) ? trim($_GPC["mobile"]) : imessage(error(-1, "请输入手机号"), "", "ajax");
            $code = trim($_GPC["code"]);
            $status = icheck_verifycode($mobile, $code);
            if (!$status) {
                imessage(error(-1, "验证码错误"), "", "ajax");
            }
            $member = pdo_fetch("select * from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and mobile = :mobile and id != :id", array(":uniacid" => $_W["uniacid"], ":mobile" => $mobile, ":id" => $id));
            if (!empty($member) && empty($force)) {
                imessage(error(-2, "该手机号已被其他用户绑定"), "", "ajax");
            }
            $salt = random(6, true);
            $password = md5(md5($salt . $password) . $salt);
            try {
                pdo_update("hello_banbanjia_members", array("mobile" => $mobile, "password" => $password, "salt" => $salt, "mobile_audit" => 1), array("id" => $id, "uniacid" => $_W["uniacid"]));
            } catch (Exception $e) {
                var_dump($e);
            }
            imessage(error(0, "绑定成功"), "", "ajax");
        } else if ($ta == 'test') {
            // echo $_W['we7_hello_banbanjia']['config']['mall']['logo'];exit;
            $img = $_W['we7_hello_banbanjia']['config']['mall']['logo'];
            var_dump(tomedia(''));
            exit;
            // var_dump($_W["member"]);exit;
        }
    }
} else if ($_GPC['from'] == 'web') { //h5接口
    if ($ta == 'register') { //h5手机号注册
        $mobile = trim($_GPC["mobile"]) ? trim($_GPC["mobile"]) : imessage(error(-1, "请输入手机号"), "", "ajax");
        $password = trim($_GPC["password"]) ? trim($_GPC["password"]) : imessage(error(-1, "密码不能为空"), "", "ajax");
        $code = trim($_GPC["code"]);
        $status = icheck_verifycode($mobile, $code);
        if (!$status) {
            imessage(error(-1, "验证码错误"), "", "ajax");
        }
        $member = pdo_fetch("select * from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and mobile = :mobile", array(":uniacid" => $_W["uniacid"], ":mobile" => $mobile));
        if (!empty($member)) {
            imessage(error(-2, "该手机号已注册,请直接登录"), "", "ajax");
        }
        $salt = random(6, true);
        $password = md5(md5($salt . $password) . $salt);
        try {
            $mall_member = array("uniacid" => $_W["uniacid"], "uid" => $fans["uid"] ? $fans["uid"] : date("His") . random(3, true), "mobile" => $mobile, "platform" => 1, "is_sys" => 1, "status" => 1, "token" => random(32), "addtime" => TIMESTAMP, "mobile_audit" => 1, "salt" => $salt, "password" => $password, "token" => random(32));
            pdo_insert("hello_banbanjia_members", $mall_member);
            $key = "we7_hello_banbanjia_member_session_" . $_W['uniacid'];
            $cookie = array('uid' => $mall_member['uid'], 'hash' => $password);
            $cookie = base64_encode(json_encode($cookie));
            isetcookie($key,$cookie,7*86400);
            imessage(error(0, "注册成功"), "", "ajax");
        } catch (Exception $e) {
            var_dump($e);
        }
    } else if ($ta == 'vercode') { //验证手机号和验证码
        $mobile = trim($_GPC["mobile"]) ? trim($_GPC["mobile"]) : imessage(error(-1, "请输入手机号"), "", "ajax");
        $code = trim($_GPC["code"]) ? trim($_GPC["code"]) : imessage(error(-1, "请输入验证码"), "", "ajax");
        $status = icheck_verifycode($mobile, $code);
        if (!$status) {
            imessage(error(-1, "验证码错误"), "", "ajax");
        } else {
            $userid = pdo_get('hello_banbanjia_members', array('mobile' => $mobile), array('id'));
            $userid = $userid['id'];
            if (empty($userid)) {
                imessage(error(-1, "请先注册"), "", "ajax");
            }
            $cache_key = "we7_hello_banbanjia:vercode:" . $_W['uniacid'] . ":" . $mobile . ":" . $userid;
            $verdata = random(5);
            cache_write($cache_key, $verdata);
            imessage(error(0, array('id' => $userid, 'verdata' => $verdata)), "", "ajax");
        }
    } else if ($ta == 'password') { //h5找回密码(重置密码)
        $mobile = trim($_GPC["mobile"]) ? trim($_GPC["mobile"]) : imessage(error(-1, "请输入手机号"), "", "ajax");
        $id = trim($_GPC["id"]) ? trim($_GPC["id"]) : imessage(error(-1, "请先验证手机号"), "", "ajax");
        $verdata1 = trim($_GPC["verdata"]) ? trim($_GPC["verdata"]) : imessage(error(-1, "请先验证手机号"), "", "ajax");
        $cache_key = "we7_hello_banbanjia:vercode:" . $_W['uniacid'] . ":" . $mobile . ":" . $id;
        $verdata = cache_read($cache_key);
        if ($verdata != $verdata1) {
            imessage(error(-1, "修改密码错误"), "", "ajax");
        } else {
            $password = trim($_GPC["password"]) ? trim($_GPC["password"]) : imessage(error(-1, "密码不能为空"), "", "ajax");
            $salt = random(6, true);
            $password = md5(md5($salt . $password) . $salt);
            $data = array("password" => $password, "salt" => $salt);
            pdo_update("hello_banbanjia_members", $data, array('id' => $id));
            imessage(error(0, "修改密码成功"), "", "ajax");
        }
    } else if ($ta == 'login') { //login
        // if(!empty($_GPC["we7_hello_banbanjia_member_session_" . $_W["uniacid"]])){
        //     imessage(error(0, "已经登录"), "", "ajax");
        // }
        $mobile = trim($_GPC["mobile"]) ? trim($_GPC["mobile"]) : imessage(error(-1, "请输入手机号"), "", "ajax");
        $member = pdo_get("hello_banbanjia_members", array("uniacid" => $_W["uniacid"], "mobile" => $mobile));
        if (empty($member)) {
            imessage(error(-1, "用户不存在"), "", "ajax");
        }
        $password = md5(md5($member["salt"] . trim($_GPC["password"])) . $member["salt"]);
        if ($password != $member["password"]) {
            imessage(error(-1, "用户名或密码错误"), "", "ajax");
        }
        $_SESSION['member'] = $member;
        // $member["hash"] = $password;
        // $key = "we7_hello_banbanjia_member_session_" . $_W["uniacid"];
        // $cookie = array("uid" => $member["uid"], "hash" => $member["hash"]);
        // $cookie = base64_encode(json_encode($cookie));
        // isetcookie($key, $cookie, 7 * 86400);
        $sessionid = $_W["session_id"];
        imessage(error(0, array('sessionid'=>$sessionid)), "", "ajax");
    }
}
