<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'slide';
if($ta == 'slide'){ //轮播图
    $slides = sys_fetch_slide('homeTop',true);

    $result = array('slides' => $slides);
    imessage(error(0,$result),'','ajax');
}