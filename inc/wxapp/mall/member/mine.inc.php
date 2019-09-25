<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth();
// var_dump('mine.inc: ',$_W['we7_hello_banbanjia']['config']);exit;
// $ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
// if($ta == 'bind'){ //用户绑定手机号

// }
$result = array('user' => $_W['member']);
imessage(error(0, $result), "", "ajax");