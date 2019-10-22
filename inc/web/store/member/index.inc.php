<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('member');
$_W["page"]["title"] = "客户概况";
$start = $_GPC["start"] ? strtotime($_GPC["start"]) : strtotime(date("Y-m"));
$end = $_GPC["end"] ? strtotime($_GPC["end"]) + 86399 : strtotime(date("Y-m-d")) + 86399;
$day_num = ($end - $start) / 86400;

$stat = member_amount_stat($sid);
include itemplate("store/member/index");