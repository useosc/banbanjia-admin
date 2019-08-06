<?php
defined('IN_IA') or exit('Access Denied');
mload()->lmodel('plugin');
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'index';
$plugins = plugin_fetchall();
if($op == 'index'){
    $_W['page']['title'] = '应用中心';
    $_W['plugin_types'] = plugin_types();
    $_W['plugins'] = array();
    foreach($plugins as $row){
        $_W['plugins'][$row['type']][] = $row;
    }
}

include itemplate('plugin/index');