<?php
defined("IN_IA") or exit("Access Denied");
mload()->lmodel("store");
global $_W;
global $_GPC;
$_W['page']['title'] = '搜索';
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
$config_mall = $_W["we7_hello_banbanjia"]["config"]["mall"];
$store_category = store_fetch_category();
if ($ta == "index") {

    $orderbys = store_orderbys();
    // $discounts = store_discounts();
    // $result = array("config" => $config_mall, "stores" => store_filter(), "orderbys" => $orderbys, "discounts" => $discounts, "carousel" => $carousel);
    $result = array("config" => $config_mall, "stores" => store_filter(), 'orderbys' => $orderbys);
    imessage(error(0, $result), "", "ajax");
}
if ($ta == 'list') {
    $lat = trim($_GPC["lat"]);
    $lng = trim($_GPC["lng"]);
    $condition = " where uniacid = :uniacid and status = 1";
    $params = array(":uniacid" => $_W["uniacid"]);
    $cid = intval($_GPC["cid"]);
    if (0 < $cid) {
        $condition .= " and cid like :cid";
        $params[":cid"] = "%|" . $cid . "|%";
    }
    $dis = trim($_GPC["dis"]);
    if (!empty($dis)) { //折扣
        if (in_array($dis, array("invoice_status"))) {
            $condition .= " and invoice_status = 1";
        } else {
            if ($dis == "delivery_price") {
                $condition .= " and (delivery_price = 0 or delivery_free_price > 0)";
            } else {
                $sids = pdo_getall("tiny_wmall_store_activity", array("uniacid" => $_W["uniacid"], "type" => $dis, "status" => 1), array("sid"), "sid");
                if (empty($sids)) {
                    $sids = array(0);
                }
                $sids = implode(",", array_keys($sids));
                $condition .= " and id in (" . $sids . ")";
            }
        }
    }
    $order_by_type = trim($_GPC["order"]) ? trim($_GPC["order"]) : $config_mall["store_orderby_type"];
    $order_by = " order by is_rest asc";
    if ($order_by_type != "distance") {
        if (in_array($order_by_type, array("sailed", "score", "displayorder", "click"))) {
            $order_by .= ", " . $order_by_type . " desc";
        } else {
            if ($order_by_type == "displayorderAndDistance") {
                $order_by .= ", displayorder desc";
            } else {
                $order_by .= ", " . $order_by_type . " asc";
            }
        }
    }
    $stores = pdo_fetchall("select * from " . tablename("hello_banbanjia_store") . (string) $condition . " " . $order_by, $params);
    $min = 0;
    if (!empty($stores)) {
        $store_label = category_store_label();
        foreach ($stores as $key => &$row) {
            $row["logo"] = tomedia($row["logo"]);
            $row["score_cn"] = round($row["score"] / 5, 2) * 100;
            if (0 < $row["label"]) {
                $row["label_color"] = $store_label[$row["label"]]["color"];
                $row["label_cn"] = $store_label[$row["label"]]["title"];
            }
            $se_fileds = array("thumbs", "sns", "payment", "qualification", "comment_reply", "data");
            foreach ($se_fileds as $se_filed) {
                if (isset($row[$se_filed])) {
                    if (!in_array($se_filed, array("thumbs", "qualification"))) {
                        $row[$se_filed] = iunserializer($row[$se_filed]);
                    } else {
                        $row[$se_filed] = iunserializer($row[$se_filed]);
                        if ($se_filed == "thumbs") {
                            foreach ($row[$se_filed] as &$thumb) {
                                $thumb["image"] = tomedia($thumb["image"]);
                            }
                        } else {
                            if ($se_filed == "qualification") {
                                foreach ($row[$se_filed] as &$thumb) {
                                    $thumb["thumb"] = tomedia($thumb["thumb"]);
                                }
                            }
                        }
                    }
                }
            }
            // $row["activity"] = store_fetch_activity($row["id"]);
            if (!empty($lng) && !empty($lat)) {
                $row["distance"] = distanceBetween($row["location_y"], $row["location_x"], $lng, $lat);
                $row["distance"] = round($row["distance"] / 1000, 2);
                // $in = is_in_store_radius($row, array($lng, $lat));
                // if ($config["store_overradius_display"] == 2 && !$in) {
                //     unset($stores[$key]);
                // }
            } else {
                $row["distance"] = 0;
            }
            $row["distance_order"] = $row["distance"] + $row["distance"] * ($row["is_rest"] == 0 ? 0 : 10000000);
            if ($order_by_type == "displayorderAndDistance" && $row["is_rest"] == 0) {
                if ($row["is_stick"] == 1) {
                    $row["distance_order"] = $row["distance_order"] / 10000 + 255 - $row["displayorder"];
                } else {
                    $row["distance_order"] = $row["distance_order"] / 10000 + (256 - $row["displayorder"]) * 10000;
                }
            }
        }
        if ($order_by_type == "distance") {
            $stores = array_sort($stores, (string) $order_by_type . "_order", SORT_ASC);
        }
        $stores = array_values($stores);
    }
    $orderbys = store_orderbys();
    $result = array("config" => $config_mall, "stores" => $stores, 'orderbys' => $orderbys);
    imessage(error(0, $result), "", "ajax");
}
