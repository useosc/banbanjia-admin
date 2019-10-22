<?php
defined("IN_IA") or exit("Access Denied");
mload()->lmodel("deliveryer");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "员工入口";
    $urls = array("business" => iurl("store/oauth/login", array(), true), "register" => imurl("manage/auth/register", array(), true), "login" => imurl("manage/auth/login", array(), true));
    include itemplate("clerk/cover");
}