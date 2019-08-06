<?php
defined('IN_IA') or exit('Access Denied');

function clerk_manage($id)
{
    global $_W;
    $permit = pdo_getall('hello_banbanjia_store_clerk', array('uniacid' => $_W['uniacid'], 'clerk_id' => $id, 'role' => 'manager'), array(), 'sid');
    if (empty($permit)) {
        return array();
    }
    return array_keys($permit);
}
