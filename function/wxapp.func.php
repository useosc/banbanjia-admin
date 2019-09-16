<?php
defined("IN_IA") or exit("Access Denied");
function imessage($msg, $redirect = "", $type = "ajax") //返回数据格式化
{
    global $_W;
    global $_GPC;

    if (is_array($msg)) {
        $msg["url"] = $redirect;
    }
    $global = array("system" => array("siteroot" => $_W["siteroot"], "attachurl" => $_W["attachurl"], "cookie_pre" => $_W["config"]["cookie"]["pre"]), "configmall" => $_W["we7_hello_banbanjia"]["config"]["mall"], "time" => $_W["timestamp"]);

    $vars = array("data" => $msg, "global" => $global, "type" => $type, "url" => $redirect);
    exit(json_encode($vars));
}

// 收集formid
function collect_wxapp_formid()
{
    global $_W;
    global $_GPC;
    if (!empty($_GPC['formid']) || !empty($_GPC['prepay_id'])) {
        $appid = $_W["we7_wxapp"]["config"]["basic"]["key"];
        $openid = $_W['openid_wxapp'];
        if($_W['_ctrl'] == 'manage'){
            $appid = $_W["we7_wxapp"]["config"]["manager"]["key"];
            $openid = $_W["manager"]["openid_wxapp_manager"];
        }else {
            if ($_W["_ctrl"] == "deliveryer") {
                $appid = $_W["we7_wxapp"]["config"]["deliveryer"]["key"];
                $openid = $_W["deliveryer"]["openid_wxapp_deliveryer"];
            }
        }
        if (empty($openid)) {
            return error(-1, "未获取到有效的openid");
        }
        $formid = trim($_GPC["formid"]);
        $times = 1;
        if (!empty($_GPC["prepay_id"])) {
            $times = 3;
            $formid = trim($_GPC["prepay_id"]);
        }
        $data = array(
            'uniacid' => $_W['uniacid'],
            'appid' => $appid,
            "openid" => $openid,
            "formid" => $formid,
            "addtime" => TIMESTAMP,
            "endtime" => TIMESTAMP + 6.5 * 86400,
            "endtime_cn" => date("Y-m-d H:i", TIMESTAMP + 6.5 * 86400)
        );
        for ($i = 0; $i < $times; $i++) {
            $is_exist = pdo_get("hello_banbanjia_wxapp_formid_log", array("uniacid" => $_W["uniacid"], "appid" => $appid, "openid" => $openid, "formid" => $formid));
            if (empty($is_exist)) {
                pdo_insert("hello_banbanjia_wxapp_formid_log", $data);
            }
        }
    }
    return true;
}
