<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('cron');
mload()->lmodel('clerk');
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "task";
set_time_limit(0);
// if ($op == "task") {
//     cron_order();
//     exit("success");
// }