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
$clerk = pdo_get("hello_banbanjia_clerk", array("uniacid" => $_W['uniacid'], 'openid_wxapp' => $_W['openid']));
if (empty($clerk) && !empty($_W["openid_wechat"])) { //微信公众号
    $clerk = pdo_get("hello_banbanjia_clerk", array("uniacid" => $_W["uniacid"], "openid" => $_W["openid_wechat"]));
}

if ($ta == 'account') {
    if (!empty($clerk)) {
        imessage(error(-1000, ''), '', 'ajax');
    }
    if ($_W['ispost']) {
        $mobile = trim($_GPC['mobile']);
        if (!is_validMobile($mobile)) {
            imessage(error(-1, "手机号格式错误"), "", "ajax");
        }
        if ($config_store['settle']['mobile_verify_status'] == 1) {
            $code = trim($_GPC["code"]);
            $status = icheck_verifycode($mobile, $code);
            if (!$status) {
                imessage(error(-1, "验证码错误"), "", "ajax");
            }
        }
        $is_exist = pdo_fetchcolumn("select id from " . tablename("hello_banbanjia_clerk") . " where uniacid = :uniacid and mobile = :mobile", array(":uniacid" => $_W["uniacid"], ":mobile" => $mobile));
        if (!empty($is_exist)) {
            imessage(error(-1, "该手机号已绑定其他员工, 请更换手机号"), "", "ajax");
        }
        $is_exist = pdo_fetchcolumn("select id from " . tablename("hello_banbanjia_clerk") . " where uniacid = :uniacid and openid_wxapp = :openid_wxapp", array(":uniacid" => $_W["uniacid"], ":openid_wxapp" => $_W["openid"]));
        if (!empty($is_exist)) {
            imessage(error(-1, "该微信信息已绑定其他员工, 请更换微信信息"), "", "ajax");
        }
        if (!empty($_W["openid_wechat"])) {
            $is_exist = pdo_fetchcolumn("select id from " . tablename("hello_banbanjia_clerk") . " where uniacid = :uniacid and openid = :openid_wechat", array(":uniacid" => $_W["uniacid"], ":openid_wechat" => $_W["openid_wechat"]));
            if (!empty($is_exist)) {
                imessage(error(-1, "该微信信息已绑定其他员工, 请更换微信信息"), "", "ajax");
            }
        }
        $password = trim($_GPC["password"]) ? trim($_GPC["password"]) : imessage(error(-1, "密码不能为空"), "", "ajax");
        $length = strlen($password);
        if ($length < 6 || 20 < $length) {
            imessage(error(-1, "请输入6-20密码"), "", "ajax");
        }
        if (!preg_match(IREGULAR_PASSWORD, $password)) {
            imessage(error(-1, "密码必须由数字和字母组合"), "", "ajax");
        }
        $data = array("uniacid" => $_W["uniacid"], "mobile" => $mobile, "title" => trim($_GPC["title"]), "openid" => $_W["openid_wechat"], "openid_wxapp" => $_W["openid"], "nickname" => $_W["member"]["nickname"], "avatar" => $_W["member"]["avatar"], "salt" => random(6), "token" => random(32), "addtime" => TIMESTAMP);
        $data["password"] = md5(md5($data["salt"] . $password) . $data["salt"]);
        pdo_insert("hello_banbanjia_clerk", $data);
        $id = pdo_insertid();
        imessage(error(-1000, "继续完善商户信息"), "", "ajax");
    }
    // $result = array("mobile_verify_status" => $config_store['settle']['mobile_verify_status'],'')
    imessage(error(0, $result), '', 'ajax');
    return 1;
}else{
    if($ta == 'store'){

    }
}
