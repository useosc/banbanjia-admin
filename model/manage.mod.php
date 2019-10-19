<?php
defined("IN_IA") or exit("Access Denied");

function icheckmanage()
{
    global $_W;
    global $_GPC;
    $_W['manager'] = array();
    if (defined("IN_WXAPP") || defined("IN_WAP") || defined("IN_APP")) {
        $token = trim($_GPC['token']);
        if (!empty($token)) {
            $clerk = pdo_get("hello_banbanjia_clerk", array("uniacid" => $_W["uniacid"], "token" => $token));
        }
    } else {
        if (is_weixin() && !defined("IN_WXAPP") && !empty($_W["openid"])) {
            $clerk = pdo_get("hello_banbanjia_clerk", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid"]));
        }
    }

    if (!empty($clerk)) {
        $_W['manager'] = $clerk;
        $_W['openid'] = $clerk['openid'];
        $_W['openid_wxapp'] = $clerk["openid_wxapp"];
    }
    if (empty($_W["manager"])) {
        $key = "we7_banbanjia_manager_session_" . $_W["uniacid"];
        if (isset($_GPC[$key])) {
            $session = json_decode(base64_decode($_GPC[$key]), true);
            if (is_array($session)) {
                $clerk = pdo_get("hello_banbanjia_clerk", array("uniacid" => $_W["uniacid"], "id" => $session["id"]));
                if (is_array($clerk) && $session["hash"] == $clerk["password"]) {
                    $_W["manager"] = $clerk;
                } else {
                    isetcookie($key, false, -100);
                }
            } else {
                isetcookie($key, false, -100);
            }
        }
    }
    if (empty($_W["openid"])) {
        $_W["openid"] = $_W["manager"]["openid"];
    }
    if (!empty($_W["manager"])) {
        return true;
    }
    imessage(error(41009, '请先登录'), '', 'ajax');
}
