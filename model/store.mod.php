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
