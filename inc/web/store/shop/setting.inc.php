<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "post";
if ($ta == "post") {
    $_W["page"]["title"] = "门店信息";
    include itemplate("store/shop/setting");
}