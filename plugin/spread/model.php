<?php
defined("IN_IA") or exit("Access Denied");

function order_spread_commission_calculate($type, $data)
{ //订单佣金计算
    global $_W;
    if (!empty($_W["member"]["spread1"]) && $_W["member"]["spreadfixed"] == 1) {
        $spread_status = (string) $type . "_status";
        $config_spread = get_plugin_config("spread");
        $config_spread["basic"]["move_status"] = 1;
        if ($config_spread["basic"][$spread_status] != 1) {
            return $data;
        }
        $spread1 = $_W["member"]["spread1"];
        if ($config_spread["basic"]["level"] == 2) {
            $spread2 = $_W["member"]["spread2"];
        }
        $spreads = pdo_fetchall("select uid,spread_groupid from " . tablename("hello_banbanjia_members") . " where uid = :uid1 or uid = :uid2", array(":uid1" => $spread1, ":uid2" => $spread2), "uid");
        if (!empty($spreads)) {
            $groups = spread_groups();
            $group1 = $groups[$spreads[$spread1]["spread_groupid"]];
            if (!empty($group1)) {
                $data["spread1"] = $spread1;
                $commission1_type = $group1["commission_type"];
                $commission1_value = $group1["commission1"];
                if ($type == "carry" || $type == "move") {
                    $commission1_type = $group1["data"][$type]["commission_type"];
                    $commission1_value = $group1["data"][$type]["commission1"];
                }
                if ($commission1_type == "ratio") {
                    $spread1_rate = $commission1_value / 100;
                    $commission_spread1 = round($spread1_rate * $data["final_fee"], 2);
                    $spread1_rate = $spread1_rate * 100;
                } else {
                    if ($commission1_type == "fixed") {
                        $commission_spread1 = $commission1_value;
                    }
                }
            }
        }
        if (!empty($spread2)) {
            $group2 = $groups[$spreads[$spread2]["spread_groupid"]];
            if (!empty($group2)) {
                $data["spread2"] = $spread2;
                $commission2_type = $group2["commission_type"];
                $commission2_value = $group2["commission2"];
                if ($type == "carry" || $type == "move") {
                    $commission2_type = $group2["data"][$type]["commission_type"];
                    $commission2_value = $group2["data"][$type]["commission2"];
                }
                if ($commission2_type == "ratio") {
                    $spread2_rate = $commission2_value / 100;
                    $commission_spread2 = round($spread2_rate * $data["final_fee"], 2);
                    $spread2_rate = $spread2_rate * 100;
                } else {
                    if ($commission2_type == "fixed") {
                        $commission_spread2 = $commission2_value;
                    }
                }
            }
        }
        if (0 < $commission_spread1 || 0 < $commission_spread2) {
            $data["spreadbalance"] = 0;
            $data["data"]["spread"] = array("commission" => array("commission1_type" => $commission1_type, "spread1_rate" => (string) $spread1_rate . "%", "spread1" => floatval($commission_spread1), "commission2_type" => $commission2_type, "spread2_rate" => (string) $spread2_rate . "%", "spread2" => floatval($commission_spread2), "from_spread" => $_SESSION["from_spread_id"]));
        }
    }
    unset($_SESSION["from_spread_id"]);
    return $data;
}
