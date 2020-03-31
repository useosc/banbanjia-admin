<?php
defined('IN_IA') or exit('Access Denied');

//获取企业客户
// function store_fetch_customers($id){
//     global $_W;
//     $customers = pdo_get("hello_banbanjia_store_customer",array("uniacid" => $_W['uniacid'],"id" => $id));
//     if (empty($customers)) {
//         return error(-1, "客户不存在或已删除");
//     }
//     return $customers;
// }

//新建顾客
function new_store_customer($data = array()) {
    global $_W;
    $insert = array(
        "uniacid" => $_W['uniacid'], "sid" => $_W['sid'],
        "name" => trim($data['name']), "mobile" => trim($data['mobile']),
        "addtime" => TIMESTAMP, "create_clerk_id" => $_W['manager']['id'],
        "owner_clerk_id" => $_W['manager']['id'], "customer_no" => TIMESTAMP
    );

    pdo_insert("hello_banbanjia_store_customers",$insert);
    $cusid = pdo_insertid();
    return $cusid;
}