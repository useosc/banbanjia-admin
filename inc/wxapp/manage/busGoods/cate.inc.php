<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list') {
    $_W["page"]["title"] = "商品列表";
    // $condition = " WHERE uniacid = :uniacid and sid = :sid";
    // $params[":uniacid"] = $_W["uniacid"];
    // $params[":sid"] = $sid;
    // $pindex = max(1, intval($_GPC["page"]));
    // $psize = 15;
    // $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("ims_hello_banbanjia_store_cate") . $condition, $params);
    // $lists = pdo_fetchall("SELECT * FROM " . tablename("ims_hello_banbanjia_store_cate") . $condition . " ORDER BY id LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    // $pager = pagination($total, $pindex, $psize);
    // $result = array('total' => $total,'list' => $lists);

    $cates = pdo_getall("hello_banbanjia_store_cate",array("uniacid" => $_W['uniacid'],"sid" => $_W['sid']));
    $cates1 = getTree($cates);

    imessage(error(0,$cates1),'','ajax');
}

if($ta == 'post') {
    $_W["page"]["title"] = "添加分类";
    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {
        $_GPC["name"] = trim($_GPC["name"]) ? trim($_GPC["name"]) : imessage(error(-1, "分类名称不能为空"), "", "ajax");
        $data = array("uniacid" => $_W["uniacid"], "sid" => $sid,  "name" => $_GPC["name"], "typeNumber" => "busgoods", "parentId" => intval($_GPC['parentId']));
        if (!$id) {
            pdo_insert("ims_hello_banbanjia_store_cate", $data);
            imessage(error(0, "新建分类成功"), "", "ajax");
        } else {
            pdo_update("ims_hello_banbanjia_store_cate", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑分类成功"), "", "ajax");
    }
    if (0 < $id) {
        $item = pdo_get("ims_hello_banbanjia_store_cate", array("uniacid" => $_W["uniacid"], "id" => $id));
        imessage(error(0, $item), "", "ajax");
    }
}

if($ta == 'del') {
    $id = intval($_GPC["id"]);
    // 查找是否有商品用了此分类
    $goods = pdo_getall("hello_banbanjia_store_goods",array("uniacid" => $_W['uniacid'],"cateid" => $id));
    if(!empty($goods)) {
        imessage(error(-1, "删除失败，不能删除已被使用的分类！"), "", "ajax");
    }
    pdo_delete("ims_hello_banbanjia_store_cate", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除分类成功"), "", "ajax");
}
