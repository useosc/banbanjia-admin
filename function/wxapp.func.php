<?php
defined("IN_IA") or exit("Access Denied");
function imessage($msg, $redirect = "", $type = "ajax") //返回数据格式化
{
    global $_W;
    global $_GPC;

    if (is_array($msg)) {
        $msg["url"] = $redirect;
    }
    $global = array("system" => array("siteroot" => $_W["siteroot"], "attachurl" => $_W["attachurl"], "cookie_pre" => $_W["config"]["cookie"]["pre"]), "cookie_pre" => $_W["config"]["cookie"]["pre"], "configmall" => $_W["hello_banbanjia"]["config"]["mall"], "time" => $_W["timestamp"]);

    $vars = array("data" => $msg, "global" => $global, "type" => $type, "url" => $redirect);
    exit(json_encode($vars));
}
