<?php

defined("IN_IA") or exit("Access Denied");
global $_W, $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';


include itemplate('dashboard/index');