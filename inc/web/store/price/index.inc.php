<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "报价概况";

include itemplate("store/price/index");