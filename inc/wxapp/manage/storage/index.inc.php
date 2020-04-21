<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list') {
    mload()->lmodel('storage');
    $records = store_storage_fetchall();
    $result = array("storages"=>$records['storages']);
    imessage(error(0,$result),'','ajax');
}
if($ta == 'post') {
    // error_reporting(E_ALL);
    $_W["page"]["title"] = "新增仓库";

    $id = intval($_GPC['id']);
    if ($_W["ispost"]) {
        $_GPC["clerkid"] = intval($_GPC["clerkid"]) ? intval($_GPC["clerkid"]) : imessage(error(-1, "负责人不能为空"), "", "ajax");
        $_GPC["name"] = trim($_GPC["name"]) ? trim($_GPC["name"]) : imessage(error(-1, "名称不能为空"), "", "ajax");

        $data = array("uniacid" => $_W["uniacid"], "sid" => $_W['sid'], "name" =>trim($_GPC['name']), "status" => intval($_GPC['status']),
        "owner_clerk_id" => $_GPC['clerkid'],"create_clerk_id" => $_W['manager']['id'],"remark" => trim($_GPC['remark']),"modifytime" => TIMESTAMP,
        "isdelete" => 0);
        if (!$id) {
            $data["addtime"] = TIMESTAMP;
            pdo_insert("hello_banbanjia_store_storage", $data);
            imessage(error(0, "新增仓库成功"), "", "ajax");
        } else {
            pdo_update("hello_banbanjia_store_storage", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
        }
        imessage(error(0, "编辑仓库成功"), "", "ajax");
    }


    if (0 < $id) {
        $item = pdo_get("hello_banbanjia_store_storage", array("uniacid" => $_W["uniacid"], "id" => $id));
        if(!empty($item)) {
            $item['addtime_cn'] = date("Y-m-d H:i:s",$item['addtime']);
            $item['modifytime_cn'] = date("Y-m-d H:i:s",$item['modifytime']);
            $item['owner'] = pdo_get("hello_banbanjia_store_clerk",array("sid" => $_W['sid'],"clerk_id" => $item['owner_clerk_id']));
        }
        imessage(error(0, $item), "", "ajax");
    }
}

if($ta == 'del') {
    $id = intval($_GPC["id"]);
    // 查找是否有商品用了此分类
    // $goods = pdo_getall("hello_banbanjia_store_goods",array("uniacid" => $_W['uniacid'],"cateid" => $id));
    // if(!empty($goods)) {
    //     imessage(error(-1, "删除失败，不能删除已被使用的分类！"), "", "ajax");
    // }
    pdo_delete("hello_banbanjia_store_storage", array("uniacid" => $_W["uniacid"], "id" => $id));
    imessage(error(0, "删除仓库成功"), "", "ajax");
}