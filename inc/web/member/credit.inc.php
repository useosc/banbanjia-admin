<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$credit = $_GPC["credit"] ? trim($_GPC["credit"]) : "credit1";
if ($credit == "credit1") {
    $_W["page"]["title"] = "积分明细";
} else {
    $_W["page"]["title"] = "余额明细";
}
