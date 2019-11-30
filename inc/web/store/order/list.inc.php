<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$_W["page"]["title"] = "订单管理";
mload()->lmodel('bpm');
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if ($ta == 'list') {
    // $users = getBpmUsers();
    $casesInfo = getBpmCases();
    if (is_error($casesInfo)) {
        imessage("获取订单信息出错", referer(), "error");
    }
    // var_dump($casesInfo);
    // exit;

    $cases = json_decode($casesInfo['content']);
    // print_r($cases);exit;
    // foreach($cases as $dca){
    //     var_dump($dca);
    // }
    // exit;

    include itemplate("store/order/list");
}

//可新建订单列表
if ($ta == 'new') {
    $startCases = getBpmStartCases();
    if (is_error($startCases)) {
        imessage("获取订单信息出错", referer(), "error");
    }
    $startCases = json_decode($startCases['content']);
    // var_dump($startCases);exit;
    include itemplate("store/order/list");
}

if ($ta == 'test') {
    $user = createBpmUsers(array(
        'usr_username' => 'kefu01',
        'usr_firstname' => 'cuihua',
        'usr_lastname' => 'zhang',
        'usr_email' => '2253428146@qq.com',
        'usr_due_date' => '2020-12-31',
        'usr_status' => 'ACTIVE',
        'usr_role' => 'PROCESSMAKER_OPERATOR',
        'usr_new_pass' => '123456',
        'usr_cnf_pass' => '123456',
        'usr_address' => '天河车陂100号'
    ));
    var_dump($user);
    exit;
}
