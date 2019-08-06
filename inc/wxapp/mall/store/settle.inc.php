<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$_W['page']['title'] = '企业入驻';
icheckauth();
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "account";
$config_store = $_W['we7_hello_banbanjia']['config']['store'];
if ($config_store["settle"]["status"] != 1) {
    imessage(error(-1, "暂时不支持商户入驻"), "", "ajax");
}

if($ta == 'account'){
    
}