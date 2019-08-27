<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;

$_W['page']['title'] = '消息通知管理';
include itemplate('notice/list');