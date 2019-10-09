<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "settle";
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