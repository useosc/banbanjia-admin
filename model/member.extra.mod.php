<?php
defined("IN_IA") or exit("Access Denied");

function member_to_black($uid, $plugin, $remark = "")
{
    global $_W;
    if (empty($uid)) {
        $uid = $_W["member"]["uid"];
    }
    $is_exist = pdo_get("hello_banbanjia_member_black", array("uniacid" => $_W["uniacid"], "uid" => $uid, "plugin" => $plugin));
    if (!empty($is_exist)) {
        return false;
    }
    $data = array("uniacid" => $_W["uniacid"], "uid" => $uid, "plugin" => $plugin, "remark" => $remark, "addtime" => TIMESTAMP);
    pdo_insert("hello_banbanjia_member_black", $data);
    return true;
}