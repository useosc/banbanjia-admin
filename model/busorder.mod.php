<?php
defined('IN_IA') or exit('Access Denied');

function store_order_fetchall($filter = array())
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
        $condition .= " and (billno like :keyword)";
        $params[":keyword"] = "%" . $keyword . "%";
    }
    $page = max(1, intval($_GPC["page"]));
    $psize = intval($_GPC["psize"]) ? intval($_GPC["psize"]) : 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_store_order") . $condition, $params);
    $orders = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_order") . $condition . " order by id desc limit " . ($page - 1) * $psize . "," . $psize, $params);
    if(!empty($orders)) {
        foreach($orders as &$order) {
            $order['addtime_cn'] = date("Y-m-d H:i:s",$order['addtime']);
            $order['modifytime_cn'] = date("Y-m-d H:i:s",$order['modifytime']);
            // $good['thumbs'] = htmlspecialchars_decode($good['thumbs']);
            // $thumbs = array();
            // if (!empty($good["thumbs"])) {
            //     $good["thumbs"] = iunserializer($good["thumbs"]);
            //     foreach ($good["thumbs"] as $val) {
            //         $thumbs[] = array("url" => tomedia($val), "filename" => $val);
            //     }
            //     $good["thumbs"] = $thumbs;
            // }
        }
    }
    $pager = pagination($total,$page,$psize);
    return array("orders" => $orders,"total" => $total,"pager" => $pager);
}