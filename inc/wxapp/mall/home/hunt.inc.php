<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
icheckauth(true);
$config_mall = $_W['we7_hello_banbanjia']['mall'];
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'index';
if ($ta == 'index') {
    if (0 < $_W["member"]["uid"]) {
        mload()->lmodel("member");
        $member = member_fetch();
    }
    $result = array('hotStores' => store_fetchall_by_condition('hot'), 'recommendStores' => store_fetchall_by_condition('recommend'), 'searchHistorys' => $member['search_data'], 'v' => $store_num);
    imessage(error(0, $result), '', 'ajax');
}
if ($ta == 'delhistory') {
    if (0 < $_W['member']['uid']) {
        pdo_update("hello_banbanjia_members", array('search_data' => ''), array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
    }
    imessage(error(0, '清除历史记录成功'), '', 'ajax');
}
if ($ta == 'search') {
    if (0 < $_W['member']['uid']) {
        mload()->lmodel('member');
        $lat = trim($_GPC['lat']);
        $lng = trim($_GPC['lng']);
        $key = trim($_GPC['key']);
        $member = member_fetch();
        if (!empty($member)) {
            $num = count($member["search_data"]);
            if (5 <= $num) {
                array_pop($member["search_data"]);
            }
            array_push($member["search_data"], $key);
            $search_data = iserializer(array_unique($member["search_data"]));
            pdo_update("hello_banbanjia_members", array("search_data" => $search_data), array("uniacid" => $_W["uniacid"], "uid" => $_W["member"]["uid"]));
        }
    }
    $key = trim($_GPC["key"]);
    $sids = array(0);
    $sids_str = 0;
    $stores = array();
    if(!empty($key)) {
    }

    $num = count($stores);
    // if()

    $result = array("stores" => array_values($store), "recommendStores" => $recommend_stores);
    imessage(error(0,$result),'','ajax');
}
