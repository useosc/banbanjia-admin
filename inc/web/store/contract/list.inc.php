<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "合同列表";
$ta = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";

include itemplate('store/contract/list');