<?php

defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'basic';
if($op == 'basic'){
    $_W['page']['title'] = '基础设置';
    $wxapp = get_plugin_config('wxapp.basic');
    if($_W['ispost']){
        $data = array('status' => intval($_GPC['status']),'audit_status' => intval($_GPC['audit_status']),'default_sid' => intval($_GPC['default_sid']),'key'=>trim($_GPC['key']),'secret'=>trim($_GPC['secret']));
        if(!empty($wxapp['release_version'])){
            $data['release_version'] = $wxapp['release_version'];
        }
        set_plugin_config('wxapp.basic',$data);
        imessage(error(0,'基础设置成功'),"refresh",'ajax');
    }
    include itemplate('config/basic');
}