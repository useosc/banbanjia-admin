<?php
defined('IN_IA') or exit('Access Denied');

function store_set_data($sid, $key, $value)
{
    global $_W;
    $data = store_get_data($sid);
    $keys = explode(".", $key);
    $counts = count($keys);
    if ($counts == 1) {
        $data[$keys[0]] = $value;
    } else {
        if ($counts == 2) {
            if (!is_array($data[$keys[0]])) {
                $data[$keys[0]] = array();
            }
            $data[$keys[0]][$keys[1]] = $value;
        } else {
            if ($counts == 3) {
                if (!is_array($data[$keys[0]])) {
                    $data[$keys[0]] = array();
                } else {
                    if (!is_array($data[$keys[0]][$keys[1]])) {
                        $data[$keys[0]][$keys[1]] = array();
                    }
                }
                $data[$keys[0]][$keys[1]][$keys[2]] = $value;
            }
        }
    }
    pdo_update("hello_banbanjia_store", array("data" => iserializer($data)), array("uniacid" => $_W["uniacid"], "id" => $sid));
    return true;
}

function store_get_data($sid, $key = "")
{
    global $_W;
    $store = pdo_get("hello_banbanjia_store", array("uniacid" => $_W["uniacid"], "id" => $sid), array("data"));
    $data = iunserializer($store["data"]);
    if (!is_array($data)) {
        $data = array();
    }
    if (empty($key)) {
        return $data;
    }
    $keys = explode(".", $key);
    $counts = count($keys);
    if ($counts == 1) {
        return $data[$key];
    }
    if ($counts == 2) {
        return $data[$keys[0]][$keys[1]];
    }
    if ($counts == 3) {
        return $data[$keys[0]][$keys[1]][$keys[2]];
    }
    return true;
}

function clerk_manage($id)
{
    global $_W;
    $permit = pdo_getall('hello_banbanjia_store_clerk', array('uniacid' => $_W['uniacid'], 'clerk_id' => $id, 'role' => 'manager'), array(), 'sid');
    if (empty($permit)) {
        return array();
    }
    return array_keys($permit);
}
function store_fetch($id, $field = array())
{
    global $_W;
    if (empty($id)) {
        return false;
    }
    $field_str = "*";
    if (!empty($field)) {
        $field[] = 'status';
        $field = array_unique($field);
        $field_str = implode(",", $field);
    }
    $data = pdo_fetch("SELECT " . $field_str . " FROM " . tablename("hello_banbanjia_store") . " WHERE uniacid = :uniacid AND id = :id", array(":uniacid" => $_W['uniacid'], ':id' => $id));
    if (empty($data)) {
        return error(-1, "企业不存在或已删除");
    }
    if ($data["status"] == 4) {
        return error(-1, "企业已删除");
    }
    $data["origin_logo"] = $data["logo"];
    $data["logo"] = tomedia($data["logo"]);
    // $cid = array_filter(explode("|", $data['cid']));
    // $data['category_arr'] = array_values($cid);
    // $cid = implode(",", $cid);
    // if (!empty($data["cid"]) && !empty($cid)) {
    //     $category = pdo_fetchall("select id,title from " . tablename("hello_banbanjia_store_category") . " where uniacid = :uniacid and id in (" . $cid . ")", array(":uniacid" => $_W['uniacid']));
    //     $data["category"] = array();
    //     if (!empty($category)) {
    //         $category_cn1 = $category_cn2 = "";
    //     }
    // }
    $se_fileds = array("thumbs", "sns", "payment", "qualification", "comment_reply", "data");
    foreach ($se_fileds as $se_filed) {
        if (isset($data[$se_filed])) {
            if (!in_array($se_filed, array("thumbs", "qualification"))) {
                $data[$se_filed] = iunserializer($data[$se_filed]);
            } else {
                $data[$se_filed] = iunserializer($data[$se_filed]);
                if ($se_filed == "thumbs") {
                    foreach ($data[$se_filed] as &$thumb) {
                        $thumb["image"] = tomedia($thumb["image"]);
                    }
                } else {
                    if ($se_filed == "qualification") {
                        foreach ($data[$se_filed] as &$thumb) {
                            $thumb["thumb"] = tomedia($thumb["thumb"]);
                        }
                    }
                }
            }
        }
    }

    $data["address_type"] = 0;

    return $data;
}

function store_status()
{
    $data = array(
        array("css" => "label label-default", "text" => "隐藏中", "color" => ""),
        array("css" => "label label-success", "text" => "已通过"),
        array("css" => "label label-info", "text" => "审核中"),
        array("css" => "label label-danger", "text" => "审核未通过"),
        array("css" => "label label-danger", "text" => "回收站")
    );
    return $data;
}
function store_manager($sid)
{
    global $_W;
    $perm = pdo_get("hello_banbanjia_store_clerk", array("uniacid" => $_W["uniacid"], "sid" => $sid, "role" => "manager"));
    $clerk = array();
    if (!empty($perm)) {
        $clerk = pdo_get("hello_banbanjia_clerk", array("uniacid" => $_W["uniacid"], "id" => $perm["clerk_id"]));
    }
    return $clerk;
}

// 获取企业所有分类
function store_category_fetchall($filter = array())
{
    global $_W;
    global $_GPC;
    if (empty($filter)) {
        $filter = $_GPC;
    } else {
        if (!isset($filter["page"])) {
            $filter["page"] = $_GPC["page"];
        }
        if (!isset($filter["psize"])) {
            $filter["psize"] = $_GPC["psize"];
        }
    }
    $parentid = intval($filter["parentid"]);
    $condition = " where uniacid = :uniacid and parentid = :parentid ";
    $params = array(":uniacid" => $_W["uniacid"], ":parentid" => $parentid);
    $status = isset($filter["status"]) ? intval($filter["status"]) : -1;
    if (-1 < $status) {
        $condition .= " and status = :status";
        $params[":status"] = $status;
    }
    $pindex = max(1, intval($filter["page"]));
    $psize = 0 < intval($filter["psize"]) ? intval($filter["psize"]) : 15;
    $data = array();
    $category = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_category") . $condition . " order by displayorder desc,id asc limit " . ($pindex - 1) * $psize . "," . $psize, $params, "id");
    if (!empty($category)) {
        foreach ($category as $key => &$val) {
            $val["thumb"] = tomedia($val["thumb"]);
            $val["child"] = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_category") . " where uniacid = :uniacid and parentid = :parentid order by displayorder desc,id asc", array(":uniacid" => $_W["uniacid"], ":parentid" => $key));
        }
    }
    $data["category"] = $category;
    return $data;
}

function store_fetchall_category($type = 'all', $filter = array())
{
    global $_W;
    global $_GPC;
    $condition = " where uniacid = :uniacid and status = 1";
    $params = array(":uniacid" => $_W["uniacid"]);
    $data = pdo_fetchall("select * from " . tablename("hello_banbanjia_store_category") . $condition . " order by displayorder desc", $params, "id");
    if (!empty($data)) {
        if ($filter["store_num"] == 1) {
            $stores = pdo_fetchall("select cate_parentid1, cate_childid1, cate_parentid2, cate_childid2 from " . tablename("hello_banbanjia_store") . $condition, $params);
        }
        foreach ($data as &$da) {
            $store_num = 0;
            $da["thumb"] = tomedia($da["thumb"]);
            $da["is_sys"] = 0;
            if ($filter["is_sys"] == 1 && empty($da["is_sys"])) {
                unset($data[$da["id"]]);
                continue;
            }
            if ($filter["store_num"] == 1) {
                if (!empty($stores)) {
                    foreach ($stores as $val) {
                        if (in_array($da["id"], $val)) {
                            $store_num++;
                        }
                    }
                }
                $da["store_num"] = $store_num;
            }
            if ($type == "parent_child") {
                if (!empty($da["parentid"])) {
                    $config_mall = $_W["we7_hello_banbanjia"]["config"]["mall"];
                    if ($config_mall["store_use_child_category"] == 1) {
                        $data[$da["parentid"]]["child"][] = $da;
                    }
                    unset($data[$da["id"]]);
                }
            } else {
                if ($type == "parent&child") {
                    $da["name"] = $da["title"];
                    if (empty($da["parentid"])) {
                        $parent[$da["id"]] = $da;
                    } else {
                        $child[$da["parentid"]][$da["id"]] = $da;
                    }
                }
            }
        }
        if ($type == "parent&child") {
            unset($data);
            $data = array("parent" => $parent, "child" => $child);
        }
    }
    return $data;
}

// 搜索店铺条件
function store_orderbys()
{
    return array(
        'distance' => array('title' => "离我最近", 'key' => 'distance', 'val' => 'asc'),
        'sailed' => array('title' => '销量最高', 'key' => 'sailed', 'val' => 'desc'),
        'score' => array('title' => '评分最高', 'key' => 'score', 'val' => 'desc'),
        'carry_time' => array('title' => '搬家速度最快', 'key' => 'carry_time', 'val' => 'asc')
    );
}

// 搜索店铺
function store_filter($filter = array(), $orderby = "")
{
    global $_W;
    global $_GPC;
    $condition = "  where uniacid = :uniacid and status = 1";
    $params = array(":uniacid" => $_W["uniacid"]);
    if (empty($filter)) {
        $filter = $_GPC;
    }

    if (0 < $filter['cid']) {
        $condition .= " and (cate_parentid1 = :parent_id or cate_parentid2 = :parent_id)";
        $params[':parent_id'] = $filter['cid'];
    }
    if (!empty($filter['ids'])) {
        $condition .= " and id in (" . $filter['ids'] . ")";
    }

    $config_mall = $_W["we7_hello_banbanjia"]["config"]["mall"];
    $lat = trim($_GPC["lat"]) ? trim($_GPC["lat"]) : "37.80081";
    $lng = trim($_GPC["lng"]) ? trim($_GPC["lng"]) : "112.57543";
    $order_by_base = " order by is_rest asc";
    // $order_by = trim($temp["order"]) ? trim($temp["order"]) : $config_mall["store_orderby_type"];
    // if (in_array($order_by, array("sailed", "score", "displayorder", "click"))) {
    //     $order_by_base .= ", " . $order_by . " desc";
    // } else {
    //     if ($order_by == "displayorderAndDistance") {
    //         $order_by_base .= ", displayorder desc, distance asc";
    //     } else {
    //         $order_by_base .= ", " . $order_by . " asc";
    //     }
    // }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = intval($_GPC["psize"]) ? intval($_GPC["psize"]) : 20;
    $limit = " limit " . ($pindex - 1) * $psize . "," . $psize;
    // $stores = pdo_fetchall("select id,agentid,cate_parentid1,cate_childid1,cate_parentid2,cate_childid2,score,title,logo,content,sailed,score,label,delivery_type,serve_radius,not_in_serve_radius,delivery_areas,business_hours,is_in_business,is_rest,is_stick,delivery_fee_mode,delivery_price,delivery_free_price,send_price,delivery_time,delivery_mode,token_status,invoice_status,location_x,location_y,forward_mode,forward_url,displayorder,click,\r\n ROUND(\r\n        6378.138 * 2 * ASIN(\r\n            SQRT(\r\n                POW(\r\n                    SIN(\r\n                        (\r\n                            " . $lat . " * 3.141592654 / 180 - location_x * 3.141592654 / 180\r\n                        ) / 2\r\n                    ),\r\n                    2\r\n                ) + COS(" . $lat . " * 3.141592654 / 180) * COS(location_x * 3.141592654 / 180) * POW(\r\n                    SIN(\r\n                        (\r\n                           " . $lng . "  * 3.141592654 / 180 - location_y * 3.141592654 / 180\r\n                        ) / 2\r\n                    ),\r\n                    2\r\n                )\r\n            )\r\n        ) * 1000) as distance from " . tablename("tiny_wmall_store") . " " . $condition . " " . $order_by_base . " " . $limit, $params, "id");

    // $result = array("stores" => array_values($stores), "total" => $total, "pagetotal" => $pagetotal);

    $stores = pdo_fetchall("select * from " . tablename("hello_banbanjia_store") . " " . $condition  . " " . $limit, $params, "id");
    $result = array('stores' => array_values($stores));
    return $result;
}

// 企业分类
function store_fetch_category()
{
    global $_W;
    global $_GPC;
    $cid = intval($_GPC["cid"]);
    // $category = pdo_get
}

function store_fetchall_by_condition($type = "hot", $option = array())
{
    global $_W;
    if (empty($option['limit'])) {
        $option['limit'] = 6;
    }
    if (empty($option["extra_type"])) {
        $option["extra_type"] = "all";
    }
    $condition = " where uniacid = :uniacid and agentid = :agentid and status = 1";
    $params = array(':uniacid' => $_W['uniacid'], ':agentid' => $_W['agentid']);
    if (isset($option["is_rest"])) {
        $condition .= " and is_rest = :is_rest";
        $params[":is_rest"] = intval($option["is_rest"]);
    }
    
}
