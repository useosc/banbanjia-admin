<?php
defined('IN_IA') or exit('Access Denied');

function store_storage_fetchall($filter = array())
{
    global $_W;
    global $_GPC;
    if(empty($filter)) {
        $filter = $_GPC;
    }
    $condition = " where uniacid = :uniacid and sid = :sid";
    $params = array(":uniacid" => $_W['uniacid']);
    $sid = intval($filter['sid']);
    if (0 < $sid) {
        $condition .= " and sid = :sid";
        $params[":sid"] = $sid;
    }
    $status = intval($filter['status']);
    if(0 < $status) {
        $condition .= " and status = :status";
        $params[':status'] = $status;
    }
    if(!empty($filter['starttime']) && !empty($filter['endtime'])){
        $condition .= " AND addtime > :start AND addtime < :end";
        $params[":start"] = $filter["starttime"];
        $params[":end"] = $filter["endtime"];
    }
    $keyword = trim($filter["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and (name like :keyword)";
        $params[":keyword"] = "%" . $keyword . "%";
    }
    $page = max(1, intval($_GPC["page"]));
    $psize = intval($_GPC["psize"]) ? intval($_GPC["psize"]) : 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_storage") . $condition, $params);
    $lists = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_storage") . $condition . " order by id desc limit " . ($page - 1) * $psize . "," . $psize, $params);
    if(!empty($lists)) {
        foreach($lists as &$item) {
            $item['addtime_cn'] = date("Y-m-d H:i:s",$item['addtime']);
            $item['modifytime_cn'] = date("Y-m-d H:i:s",$item['modifytime']);
            // $item['owner'] = pdo_get("hello_banbanjia_store_");
            $item['owner'] = pdo_get("hello_banbanjia_store_clerk",array("sid" => $_W['sid'],"clerk_id" => $item['owner_clerk_id']));
        }
    }
    $pager = pagination($total,$page,$psize);
    return array("storages" => $lists,"total" => $total,"pager" => $pager);
}