<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "settle";
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
mload()->lmodel('store');
if ($op == "settle") {
    $_W["page"]["title"] = "商户入驻";
    if ($_W["ispost"]) {
        $settle = array("status" => intval($_GPC["status"]), "audit_status" => intval($_GPC["audit_status"]), "mobile_verify_status" => intval($_GPC["mobile_verify_status"]), "qualification_verify_status" => intval($_GPC["qualification_verify_status"]), "store_label_new" => intval($_GPC["store_label_new"]));
        set_config_text("商户入驻协议", "agreement_settle", htmlspecialchars_decode($_GPC["agreement_settle"]));
        set_system_config("store.settle", $settle);
        imessage(error(0, "商户入驻设置成功"), referer(), "ajax");
    }
    $settle = $_config["store"]["settle"];
    $settle["agreement_settle"] = get_config_text("agreement_settle");
    include itemplate("config/settle");
}
if ($op == 'category') {
    if ($ta == 'list') {
        $_W["page"]["title"] = "企业分类";
        $all_categorys = store_category_fetchall();
        $categorys = $all_categorys['category'];
        $pager = $all_categorys['pager'];
    } else {
        if ($ta == 'post') {
            $_W["page"]["title"] = "编辑分类";
            $id = intval($_GPC["id"]);
            if (0 < $id) {
                $category = pdo_get("hello_banbanjia_store_category", array("uniacid" => $_W["uniacid"], "id" => $id));
            }
            if ($_W["ispost"]) {
                $data = array("uniacid" => $_W["uniacid"], "title" => trim($_GPC["title"]), "thumb" => trim($_GPC["thumb"]), "status" => intval($_GPC["status"]), "parentid" => intval($_GPC["parentid"]), "displayorder" => intval($_GPC["displayorder"]));
                if (empty($_GPC["id"])) {
                    pdo_insert("hello_banbanjia_store_category", $data);
                } else {
                    pdo_update("hello_banbanjia_store_category", $data, array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
                }
                imessage(error(0, "编辑分类成功"), iurl("config/store/category/list"), "ajax");
            }
        } else {
            if ($ta == 'del') {
                $id = intval($_GPC["id"]);
                pdo_delete("hello_banbanjia_store_category", array("uniacid" => $_W["uniacid"], "id" => $id));
                imessage(error(0, "删除分类成功"), iurl("config/store/category/list"), "ajax");
            }else{
                if ($ta == "status") {
                    $id = intval($_GPC["id"]);
                    $status = intval($_GPC["status"]);
                    pdo_update("hello_banbanjia_store_category", array("status" => $status), array("uniacid" => $_W["uniacid"], "id" => $id));
                    imessage(error(0, ""), "", "ajax");
                }
            }
        }
    }
    include itemplate("config/storeCategory");
}
