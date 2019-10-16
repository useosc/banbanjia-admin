<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "post";
if ($ta == "post") {
    $_W["page"]["title"] = "企业信息";
    $id = $_W["we7_hello_banbanjia"]["sid"];
    if ($id) {
        $item = store_fetch($id);
        if (empty($item)) {
            imessage("公司信息不存在或已删除", "referer", "error");
        } else {
            $item["map"] = array("lat" => $item["location_x"], "lng" => $item["location_y"]);
        }

    }
    if ($_W['ispost']) {
        $data = array(
            'title' => trim($_GPC['title']),
            'logo' => trim($_GPC['logo']),
            'telephone' => trim($_GPC['telephone']),
            "content" => trim($_GPC["content"]),
            'description' => htmlspecialchars_decode($_GPC["description"]),
            "address" => trim($_GPC["address"]),
            "location_x" => $_GPC["map"]["lat"],
            "location_y" => $_GPC["map"]["lng"],
            "sns" => iserializer(array("qq" => trim($_GPC["sns"]["qq"]), "weixin" => trim($_GPC["sns"]["weixin"]))),
            "qualification" => iserializer(array(
                "business" => array("thumb" => trim($_GPC["qualification"]["business"])),
                "more1" => array("thumb" => trim($_GPC["qualification"]["more1"])),
                "more2" => array("thumb" => trim($_GPC["qualification"]["more2"]))
            )),
            "notice" => trim($_GPC["shopnotice"]),
            // "comment_status" => intval($_GPC["comment_status"]),
        );
        if (!empty($_GPC["thumbs"]["image"])) {
            $thumbs = array();
            foreach ($_GPC["thumbs"]["image"] as $key => $image) {
                if (empty($image)) {
                    continue;
                }
                $thumbs[] = array("image" => $image);
            }
            $data["thumbs"] = iserializer($thumbs);
        } else {
            $data["thumbs"] = "";
        }
        $info = pdo_update("hello_banbanjia_store", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        $sid = $id;
        imessage(error(0, "编辑公司信息成功"), iurl("store/shop/setting", array("_sid" => $sid)), "ajax");
    }

    include itemplate("store/shop/setting");
}
