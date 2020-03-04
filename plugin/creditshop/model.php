<?php
defined("IN_IA") or exit("Access Denied");

function creditshop_goods_get($goods_id)
{
    global $_W;
    if (empty($goods_id)) {
        return error(-1, "请输入商品编号");
    }
    $data = pdo_get("hello_banbanjia_creditshop_goods", array("uniacid" => $_W["uniacid"], "id" => $goods_id));
    if (!empty($data)) {
        $data["records_num"] = pdo_fetchcolumn("select count(*) FROM " . tablename("hello_banbanjia_creditshop_order_new") . " where uniacid = :uniacid and goods_id = :goods_id ", array(":uniacid" => $_W["uniacid"], ":goods_id" => $goods_id));
        $data["thumb"] = tomedia($data["thumb"]);
        if ($data["type"] == "redpacket") {
            $data["redpacket"] = iunserializer($data["redpacket"]);
        }
    }
    return $data;
}

function creditshop_category_get($status = -1)
{
    global $_W;
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    if ($status != -1) {
        $condition .= " and status = " . $status;
    }
    $data = pdo_fetchall("select * from " . tablename("hello_banbanjia_creditshop_category") . " " . $condition . " order by displayorder desc", $params);
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["thumb"] = tomedia($value["thumb"]);
        }
    }
    return $data;
}

function creditshop_adv_get($status = -1)
{
    global $_W;
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    if ($status != -1) {
        $condition .= " and status = " . $status;
    }
    $data = pdo_fetchall("select * from" . tablename("hello_banbanjia_creditshop_adv") . " " . $condition . " order by displayorder desc", $params);
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["thumb"] = tomedia($value["thumb"]);
        }
    }
    return $data;
}

function creditshop_goodsall_get($filter = array())
{
    global $_W;
    global $_GPC;
    if (empty($filter)) {
        if (!empty($_GPC["type"])) {
            $filter["type"] = trim($_GPC["type"]);
        }
        if (!empty($_GPC["title"])) {
            $filter["title"] = trim($_GPC["title"]);
        }
        if (!empty($_GPC["category_id"])) {
            $filter["category_id"] = intval($_GPC["category_id"]);
        }
    }
    if (empty($filter["page"])) {
        $filter["page"] = max(1, $_GPC["page"]);
    }
    if (empty($filter["psize"])) {
        $filter["psize"] = intval($_GPC["psize"]) ? intval($_GPC["psize"]) : 20;
    }
    $condition = " where uniacid = :uniacid and status = 1";
    $params = array(":uniacid" => $_W["uniacid"]);
    if (!empty($filter["type"])) {
        $condition .= " and type = :type";
        $params[":type"] = $filter["type"];
    }
    if (!empty($filter["title"])) {
        $condition .= " AND title LIKE '%" . $filter["title"] . "%'";
    }
    if (!empty($filter["category_id"])) {
        $condition .= " and category_id = :category_id";
        $params[":category_id"] = $filter["category_id"];
    }
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_creditshop_goods") . " " . $condition . " ORDER BY displayorder DESC LIMIT " . ($filter["page"] - 1) * $filter["psize"] . ", " . $filter["psize"], $params);
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["thumb"] = tomedia($value["thumb"]);
            if ($value["type"] == "redpacket") {
                $value["redpacket"] = iunserializer($value["redpacket"]);
            }
        }
    }
    return $data;
}

function creditshop_can_exchange_goods($idOrGoods, $uid = "")
{
    global $_W;
    $goods = $idOrGoods;
    if (!is_array($goods)) {
        $goods = creditshop_goods_get($goods);
    }
    if (empty($goods)) {
        return error(-1, "商品不存在！");
    }
    if (empty($uid)) {
        $uid = $_W["member"]["uid"];
    }
    $records_num = pdo_fetchcolumn("select count(*) FROM " . tablename("hello_banbanjia_creditshop_order_new") . " where uniacid = :uniacid and uid = :uid and goods_id = :goods_id ", array(":uniacid" => $_W["uniacid"], "uid" => $uid, ":goods_id" => $goods["id"]));
    if ($goods["chance"] <= $records_num) {
        return error(-2, "兑换已达最大次数！");
    }
    return error(0, "可以兑换！");
}

function creditshop_record_get($filter = array())
{
    global $_W;
    global $_GPC;
    if (empty($filter)) {
        if (!empty($_GPC["id"])) {
            $filter["goods_id"] = intval($_GPC["id"]);
        } else {
            return error(-1, "请输入商品编号");
        }
    }
    if (empty($filter["page"])) {
        $filter["page"] = max(1, $_GPC["page"]);
    }
    if (empty($filter["psize"])) {
        $filter["psize"] = intval($_GPC["psize"]) ? intval($_GPC["psize"]) : 15;
    }
    $data = pdo_fetchall("select a.addtime, b.avatar, b.nickname FROM " . tablename("hello_banbanjia_creditshop_order_new") . " as a left join " . tablename("hello_banbanjia_members") . " as b on a.uid = b.uid where a.uniacid = :uniacid and a.goods_id = :goods_id limit " . ($filter["page"] - 1) * $filter["psize"] . ", " . $filter["psize"], array(":uniacid" => $_W["uniacid"], ":goods_id" => $filter["goods_id"]));
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["addtime"] = date("Y/m/d H:i", $value["addtime"]);
        }
    }
    return $data;
}

function creditshop_order_get($id)
{
    global $_W;
    $data = pdo_get("hello_banbanjia_creditshop_order_new", array("uniacid" => $_W["uniacid"], "id" => $id));
    if (!empty($data)) {
        $data["data"] = iunserializer($data["data"]);
        $data["addtime"] = date("Y/m/d H:i", $data["addtime"]);
        $goods = creditshop_goods_get($data["goods_id"]);
        $data["goods_info"] = $goods;
        // $data["qrcode"] = ipurl("pages/plugin/creditshop/detail", array("id" => $data["id"], "code" => $data["code"]), true);
    }
    return $data;
}
function creditshop_order_update($orderOrId, $type, $extra = array())
{
    global $_W;
    $order = $orderOrId;
    if (!is_array($order)) {
        $order = creditshop_order_get($order);
    }
    if (empty($order)) {
        return error(-1, "商品不存在！");
    }
    if ($type == "pay") {
        $update = array("is_pay" => 1, "pay_type" => $extra["type"], "paytime" => TIMESTAMP);
        pdo_update("hello_banbanjia_creditshop_order_new", $update, array("id" => $order["id"]));
        if ($order["goods_type"] == "redpacket") {
            mload()->lmodel("redPacket");
            $redpacket = $order["data"]["redpacket"];
            $data = array("title" => $redpacket["name"], "channel" => "creditShop", "type" => "grant", "discount" => $redpacket["discount"], "days_limit" => $redpacket["use_days_limit"], "grant_days_effect" => $redpacket["grant_days_effect"], "condition" => $redpacket["condition"], "uid" => $order["uid"]);
            $category_limit = array();
            if (!empty($redpacket["category"])) {
                foreach ($redpacket["category"] as $category) {
                    $category_limit[] = $category["id"];
                }
            }
            $data["category_limit"] = implode("|", $category_limit);
            $res = redPacket_grant($data);
            if (!is_error($res)) {
                pdo_update("hello_banbanjia_creditshop_order_new", array("grant_status" => 1, "status" => 2), array("id" => $order["id"]));
            } else {
                return $res;
            }
        } else {
            if ($order["goods_type"] == "credit2") {
                $res = member_credit_update($order["uid"], "credit2", $order["data"]["credit2"]);
                if (!is_error($res)) {
                    pdo_update("hello_banbanjia_creditshop_order_new", array("grant_status" => 1, "status" => 2), array("id" => $order["id"]));
                }
            }
        }
        // creditshop_order_manager_notice($order["id"], "pay");
        // creditshop_order_notice($order["id"], "pay");
    } else {
        if ($type == "handle") {
            if ($order["status"] == 2) {
                return error(-1, "该订单已处理，请勿重复处理");
            }
            if ($order["status"] == 1) {
                pdo_update("hello_banbanjia_creditshop_order_new", array("status" => 2, "grant_status" => 1), array("id" => $order["id"]));
            }
            creditshop_order_notice($order["id"], "handle");
        } else {
            if ($type == "cancel" && $order["is_pay"] == 0 && $order["status"] == 1 && 0 < $order["use_credit1"] && $order["use_credit1_status"] == 1 && 0 < $order["use_credit2"]) {
                $status = member_credit_update($order["uid"], "credit1", $order["use_credit1"]);
                if (is_error($status)) {
                    imessage(-1, $status["message"], "", "ajax");
                }
                pdo_update("hello_banbanjia_creditshop_order_new", array("status" => 3), array("id" => $order["id"]));
            }
        }
    }
    return true;
}
function creditshop_orderall_get($filter = array())
{
    global $_W;
    global $_GPC;
    if (empty($filter["page"])) {
        $filter["page"] = max(1, $_GPC["page"]);
    }
    if (empty($filter["psize"])) {
        $filter["psize"] = intval($_GPC["psize"]) ? intval($_GPC["psize"]) : 6;
    }
    $condition = " where a.uniacid = :uniacid and a.uid = :uid";
    $params = array(":uniacid" => $_W["uniacid"], ":uid" => $_W["member"]["uid"]);
    $data = pdo_fetchall("select a.*, c.title, c.thumb from " . tablename("hello_banbanjia_creditshop_order_new") . " as a left join " . tablename("hello_banbanjia_creditshop_goods") . " as c on a.goods_id = c.id " . $condition . " order by a.id desc limit " . ($filter["page"] - 1) * $filter["psize"] . ", " . $filter["psize"], $params);
    if (!empty($data)) {
        foreach ($data as &$value) {
            $value["addtime"] = date("Y/m/d H:i", $value["addtime"]);
            $value["data"] = iunserializer($value["data"]);
            $value["thumb"] = tomedia($value["thumb"]);
        }
    }
    return $data;
}