<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('deliveryer');
if ($_W["_ac"] != "auth") {
    icheckdeliveryer();
    $_deliveryer = $deliveryer = $_W["deliveryer"];
    // $relation = deliveryer_push_token($_W["deliveryer"]);
    // $_W["wxapp"]["jpush_relation"] = $relation;
    // collect_wxapp_formid();
}
$config_carry = $_W["we7_hello_banbanjia"]["config"]["carry"];
$config_delivery = $_W["we7_hello_banbanjia"]["config"]["delivery"];
$_W["role"] = "deliveryer";
$_W["role_cn"] = "搬运工:" . $_W["deliveryer"]["title"];
if (!empty($_GPC["filter"])) {
    $_GPC["filter"] = json_decode(htmlspecialchars_decode($_GPC["filter"]), true);
    foreach ($_GPC["filter"] as $key => $val) {
        $_GPC[$key] = $val;
    }
}
