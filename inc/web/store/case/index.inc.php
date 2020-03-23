<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if ($ta == 'list') {
    $_W["page"]["title"] = "案例列表";

    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_store_case") . $condition, $params);
    $cases = pdo_fetchall("select * from" . tablename("hello_banbanjia_store_case") . $condition . " limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
}
if ($ta == 'post') {
    $_W["page"]["title"] = "案例编辑";
    $id = intval($_GPC["id"]);
    if (0 < $id) {
        $case = pdo_get("hello_banbanjia_store_case", array("uniacid" => $_W["uniacid"], "id" => $id));
        if(!empty($case['thumbs'])){
            $case['thumbs'] = iunserializer($case['thumbs']);
            foreach ($case['thumbs'] as &$thumb) {
                $thumb['image'] = tomedia($thumb['image']);
            }
        }
    }

    if ($_W['ispost']) {
        $data = array('uniacid' => $_W['uniacid'], 'sid' => $_W["we7_hello_banbanjia"]["sid"], 'title' => trim($_GPC['title']), 'content' =>  htmlspecialchars_decode($_GPC["content"]), 'lasttime' => TIMESTAMP);
        if (!empty($_GPC['thumbs']['image'])) {
            $thumbs = array();
            foreach ($_GPC['thumbs']['image'] as $key => $image) {
                if (empty($image)) {
                    continue;
                }
                $thumbs[] = array("image" => $image);
            }
            $data["thumbs"] = iserializer($thumbs);
        } else {
            $data["thumbs"] = "";
        }
        if (!empty($id)) {
            pdo_update("hello_banbanjia_store_case", $data, array("uniacid" => $_W['uniacid'], 'id' => $id));
        } else {
            pdo_insert("hello_banbanjia_store_case", $data);
        }
        imessage(error(0, "更新案例成功"), iurl("store/case/index"), "ajax");
    }
}
if($ta == 'del'){
    $id = intval($_GPC['id']);
    pdo_delete("hello_banbanjia_store_case", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除案例成功"), iurl("store/case/index"), "ajax");
}
if ($op == "status") {
    $id = intval($_GPC["id"]);
    $data = array("status" => intval($_GPC["status"]));
    pdo_update("hello_banbanjia_store_case", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, ""), "", "ajax");
}
include itemplate("store/case/index");
