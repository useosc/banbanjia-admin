<?php
error_reporting(E_ALL ^ E_NOTICE);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
$config = $_config_plugin["relate"];
if ($op == "index") {
    $_W["page"]["title"] = "推广员等级";
    $groups = pdo_fetchall("select * from " . tablename("hello_banbanjia_spread_groups") . " where uniacid = :uniacid", array(":uniacid" => $_W["uniacid"]));
    if (!empty($groups)) {
        foreach ($groups as &$val) {
            $val["data"] = iunserializer($val["data"]);
        }
    }
}
if ($op == "post") {
    $_W["page"]["title"] = "编辑推广员等级";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $ad = pdo_get("hello_banbanjia_spread_groups", array("uniacid" => $_W["uniacid"], "id" => $id));
        $ad["data"] = iunserializer($ad["data"]);
        $carry = $ad["data"]["carry"];
        $move = $ad["data"]["move"];
        if ($_W["ispost"]) {
            $commission_type = trim($_GPC["commission_type"]);
            if (empty($commission_type)) {
                imessage(error(-1, "请选择推广佣金计算方式"), "", "ajax");
            }
            if ($commission_type == "ratio") {
                $commission1 = floatval($_GPC["commission1_ratio"]);
                $commission2 = floatval($_GPC["commission2_ratio"]);
            } else {
                if ($commission_type == "fixed") {
                    $commission1 = floatval($_GPC["commission1_fixed"]);
                    $commission2 = floatval($_GPC["commission2_fixed"]);
                }
            }
            $data = array("uniacid" => $_W["uniacid"], "commission_type" => $commission_type, "title" => trim($_GPC["title"]), "commission1" => $commission1, "commission2" => $commission2, "become_child_limit" => intval($_GPC["group_become_child_limit"]), "valid_period" => trim($_GPC["valid_period"]), "admin_update_rules" => trim($_GPC["admin_update_rules"]), "group_condition" => floatval($_GPC["group_condition"]));
            $carry_commission_type = trim($_GPC["carry_commission_type"]);
            $data["data"]["carry"] = array("commission_type" => $carry_commission_type, "commission1" => floatval($_GPC["carry_commission1_" . $carry_commission_type]), "commission2" => floatval($_GPC["carry_commission2_" . $carry_commission_type]));

            $move_commission_type = trim($_GPC["move_commission_type"]);
            $data["data"]["move"] = array("commission_type" => $move_commission_type, "commission1" => floatval($_GPC["move_commission1_" . $move_commission_type]), "commission2" => floatval($_GPC["move_commission2_" . $move_commission_type]));
            $data["data"] = iserializer($data["data"]);
            if (!empty($ad["id"])) {
                pdo_update("hello_banbanjia_spread_groups", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
            } else {
                pdo_insert("hello_banbanjia_spread_groups", $data);
            }
            imessage(error(0, "更新推广员等级成功"), iurl("spread/groups/index"), "ajax");
        }
    }
}
if ($op == "del") {
    $id = intval($_GPC["id"]);
    pdo_delete("hello_banbanjia_spread_groups", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除推广员等级成功"), iurl("spread/groups/index"), "ajax");
}
include itemplate("groups");
