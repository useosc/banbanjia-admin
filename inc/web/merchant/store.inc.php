<?php
// error_reporting(E_ALL);
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if($op == 'list'){
    $_W['page']['title'] = '企业列表';

    $condition = " uniacid = :uniacid and (status = 1 or status = 0)";
    $params[":uniacid"] = $_W["uniacid"];

    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store") . " WHERE " . $condition, $params);
    $lists = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_store") . " WHERE " . $condition . " ORDER BY displayorder DESC,id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    // if (!empty($lists)) {
    //     foreach ($lists as &$li) {
    //         $li["wechat_qrcode"] = (array) iunserializer($li["wechat_qrcode"]);
    //         $li["wechat_url"] = $li["wechat_qrcode"]["url"];
    //     }
    // }
    include itemplate('merchant/list');
}
if($op == 'post'){
    $_W['page']['title'] = '添加企业';
    
    include itemplate('merchant/post');
}