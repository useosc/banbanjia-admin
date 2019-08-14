<?php

use function Qiniu\json_decode;

defined("IN_IA") or exit("Access Denied");
function icheckdeliveryer()
{
    global $_W;
    global $_GPC;
    $_W['deliveryer'] = array();
    if (defined("IN_WXAPP")) {
        $token = trim($_GPC["token"]);
        if (!empty($token)) {
            $deliveryer = deliveryer_fetch($token, "token");
            if (!empty($deliveryer) && empty($deliveryer["openid_wxapp_deliveryer"])) {
                $oauth = pdo_get("hello_banbanjia_oauth_fans", array("openid" => $token), array("oauth_openid"));
                if (!empty($oauth["oauth_openid"])) {
                    pdo_update("hello_banbanjia_deliveryer", array("openid_wxapp_deliveryer" => $oauth["oauth_openid"]), array("uniacid" => $_W["uniacid"], "id" => $deliveryer["id"]));
                    $deliveryer["openid_wxapp_deliveryer"] = $oauth["oauth_openid"];
                }
            }
        }
    }

    if (!empty($deliveryer)) {
        $_W["deliveryer"] = $deliveryer;
    }

    if (empty($_W['deliveryer'])) {
        $key = "we7_hello_banbanjia_deliveryer_session_" . $_W['uniacid'];
        if (isset($_GPC[$key])) {
            $session = json_decode(base64_decode($_GPC[$key]), true);
            if (is_array($session)) {
                $deliveryer = deliveryer_fetch($session['id'], 'id');
                if (is_array($deliveryer) && $session["hash"] == $deliveryer["password"]) {
                    $_W["deliveryer"] = $deliveryer;
                } else {
                    isetcookie($key, false, -100);
                }
            } else {
                isetcookie($key, false, -100);
            }
        }
    }

    if (!empty($_W["deliveryer"])) {
        if (empty($_W["deliveryer"]["openid_wxapp"]) && !empty($_W["deliveryer"]["openid"])) {
            $openid_wxapp = member_openid2wxapp($_W["deliveryer"]["openid"]);
            if (!empty($openid_wxapp)) {
                $_W["deliveryer"]["openid_wxapp"] = $openid_wxapp;
                pdo_update("hello_banbanjia_deliveryer", array("openid_wxapp" => $openid_wxapp), array("id" => $_W["deliveryer"]["id"]));
            }
        }
        if (!empty($_W["deliveryer"]["openid_wxapp"]) && empty($_W["deliveryer"]["openid"])) {
            $openid = member_wxapp2openid($_W["deliveryer"]["openid_wxapp"]);
            if (!empty($openid)) {
                $_W["deliveryer"]["openid"] = $openid;
                pdo_update("hello_banbanjia_deliveryer", array("openid" => $openid), array("id" => $_W["deliveryer"]["id"]));
            }
        }
        $_W["openid"] = $_W["deliveryer"]["openid"];
        $_W["openid_wxapp"] = $_W["deliveryer"]["openid_wxapp"];
        $sids = pdo_fetchall("select sid from " . tablename("hello_banbanjia_store_deliveryer") . " where uniacid = :uniacid and deliveryer_id = :deliveryer_id and sid > 0", array(":uniacid" => $_W["uniacid"], ":deliveryer_id" => $_W["deliveryer"]["id"]), "sid");
        $_W["deliveryer"]["sids"] = array_unique(array_keys($sids));
        $_W["deliveryer"]["sids_sn"] = implode(",", $_W["deliveryer"]["sids"]);
        return true;
    }
    if (defined("IN_WXAPP") || defined("IN_VUE")) {
        imessage(error(41009, "请先登录"), "", "ajax");
    }
}

//获取搬运工
function deliveryer_fetch($value, $field = 'id')
{
    global $_W;
    $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "status" => 1, $field => trim($value)));
    if (!empty($deliveryer)) {
        if (empty($deliveryer["token"])) {
            $deliveryer["token"] = random(32);
            pdo_update("hello_banbanjia_deliveryer", array("token" => $deliveryer["token"]), array("id" => $deliveryer["id"]));
        }
    }
    return $deliveryer;
}

//获取所有搬运工
function deliveryer_all($force_update = false)
{
    global $_W;
    $cache_key = "we7_hello_banbanjia:deliveryers:" . $_W["uniacid"];
    $data = cache_read($cache_key);
    if (!empty($data) && !$force_update) {
        return $data;
    }
    $condition = " where uniacid = :uniacid and status = :status";
    $params = array(":uniacid" => $_W["uniacid"], ":status" => 1);
    $deliveryers = pdo_fetchall("select * from " . tablename("hello_banbanjia_deliveryer") . $condition, $params, "id");
    cache_write($cache_key, $deliveryers);
    return $deliveryers;
}

//搬运工入驻通知
function sys_notice_deliveryer_settle($deliveryer_id, $note = "")
{
    global $_W;
    $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "id" => $deliveryer_id));
    if (empty($deliveryer)) {
        return error(-1, "搬运工不存在");
    }
    if ($deliveryer["status"] != 1) {
        return error(-1, "搬运工已被删除");
    }
    $maneger = $_W["we7_hello_banbanjia"]["config"]["manager"];
    if (empty($maneger["openid"])) {
        return error(-1, "平台管理员信息不存在");
    }
    $tips = "尊敬的【" . $maneger["nickname"] . "】，有新的搬运工提交了入驻请求。请登录电脑进行权限分配";
    $remark = array("性别 : " . $deliveryer["sex"], "年龄 : " . $deliveryer["age"], "申请人手机号: " . $deliveryer["mobile"], $note);
    $remark = implode("\n", $remark);
    $send = array(
        "first" => array("value" => $tips, "color" => "#ff510"),
        "keyword1" => array("value" => $deliveryer["title"], "color" => "#ff510"),
        "keyword2" => array("value" => $deliveryer["title"], "color" => "#ff510"),
        "keyword3" => array("value" => date("Y-m-d H:i", time()), "color" => "#ff510"),
        "remark" => array("value" => $remark, "color" => "#ff510")
    );
    $acc = WeAccount::create($_W["acid"]);
    $status = $acc->sendTplNotice($maneger["openid"], $_W["we7_hello_banbanjia"]["config"]["notice"]["wechat"]["settle_apply_tpl"], $send);
    if (is_error($status)) {
        slog("wxtplNotice", "平台搬运工入驻微信通知平台管理员", $send, $status["message"]);
    }
    return $status;
}
