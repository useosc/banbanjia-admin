<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth();
// if($ta == 'bind'){ //用户绑定手机号
// }
if ($_config_wxapp['diy']['use_diy_member'] != 1) {
    $user = $_W['member'];
    $favorite = intval(pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_favorite") . " where uniacid = :uniacid and uid = :uid", array(":uniacid" => $_W['uniacid'], ":uid" => $_W['member']['uid'])));
} else {
    $id = $_config_wxapp['diy']['showPage']['member'];
    if (empty($id)) {
        imessage(error(-1, '未设置会员中心DIY页面'), '', 'ajax');
    }
    mload()->lmodel('diy');
    $page = get_wxapp_diy($id, true);
    if (empty($page)) {
        imessage(error(-1, '页面不能为空'), '', 'ajax');
    }
    $result = array('is_use_diy' => 1, 'diy' => $page, 'user' => $_W['member']);
}
$result = array('user' => $_W['member']);
imessage(error(0, $result), "", "ajax");
