<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth();

// $ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
// if($ta == 'bind'){ //用户绑定手机号

// }
$result = array('user' => $_W['member']);
imessage(error(0, $result), "", "ajax");