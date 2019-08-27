<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'list';

if ($ta == 'list') {
    $result = array();
    $roomCategory = pdo_fetchall("select * from" . tablename("hello_banbanjia_room_category") . " where uniacid = :uniacid  order by displayorder", array(":uniacid" => $_W["uniacid"]));
    if (!empty($roomCategory)) {
        foreach ($roomCategory as $item) {
            $goods = pdo_fetchall("select g.* from" . tablename("hello_banbanjia_room_category") . " as r inner join" . tablename("hello_banbanjia_goods") . " as g on g.cateid = r.id and g.uniacid = :uniacid and g.is_display = 1 and r.id = :rid order by g.displayorder", array(":uniacid" => $_W["uniacid"],":rid"=>$item['id']));
            $result[] = array("rid" => $item["id"], "rtitle" => $item["title"], "rthumb" => $item['thumb'], "goods" => $goods);
        }
    }
    imessage(error(0, $result), '', 'ajax');
}
