<?php
defined("IN_IA") or exit("Access Denied");
pload()->model("gohome");
function ask_get_categorys($filter = array(), $field = array())
{
    global $_W;
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $agentid = intval($filter["agentid"]) ? intval($filter["agentid"]) : $_W["agentid"];
    if (!empty($agentid)) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $status = isset($filter["status"]) ? intval($filter["status"]) : -1;
    if (-1 < $status) {
        $condition .= " and status = :status";
        $params[":status"] = $status;
    }
    $type = empty($filter["type"]) ? "parent_child" : trim($filter["type"]);
    if ($type == "parent") {
        $condition .= " and parentid = 0";
    }
    $field_str = "*";
    if (!empty($field)) {
        $field_str = implode(",", $field);
    }
    $categorys = pdo_fetchall("select " . $field_str . " from " . tablename("hello_banbanjia_ask_category") . $condition . " order by displayorder desc", $params, "id");

    if (!empty($categorys)) {
        foreach ($categorys as &$val) {
            if (isset($val['thumb'])) {
                $val['thumb'] = tomedia($val['thumb']);
            }
            if (isset($val['config'])) {
                $val['config'] = iunserializer($val['config']);
            }
            if ($type == "parent_child") {
                if (!empty($val['parentid'])) {
                    $categorys[$val['parentid']]['child'][] = $val;
                    unset($categorys[$val['id']]);
                }
            } else {
                if ($type == "parent&child") {
                    $val["name"] = $val["title"];
                    if (empty($val["parentid"])) {
                        $parent[$val["id"]] = $val;
                    } else {
                        $child[$val["parentid"]][$val["id"]] = $val;
                    }
                }
            }
        }
        if ($type == "parent&child") {
            unset($categorys);
            $categorys = array("parent" => $parent, "child" => $child);
        }
    }
    return $categorys;
}
function ask_get_category($id, $field = array())
{
    global $_W;
    $condition = " where uniacid = :uniacid and id = :id";
    $params = array(":uniacid" => $_W["uniacid"], ":id" => $id);
    $field_str = "*";
    if (!empty($field)) {
        $field_str = implode(",", $field);
    }
    $category = pdo_fetch("select " . $field_str . " from " . tablename("hello_banbanjia_ask_category") . $condition, $params);
    if (!empty($category)) {
        $category["thumb"] = tomedia($category["thumb"]);
        if (isset($category["config"])) {
            $category["config"] = iunserializer($category["config"]);
        }
    }
    return $category;
}

function ask_get_answers($id)
{
    global $_W;
    $ansers = pdo_fetchall("select * from " . tablename("hello_banbanjia_ask_answer") . " where uniacid = :uniacid and aid = :aid ORDER BY id asc",array(":uniacid"=>$_W['uniacid'],':aid'=>$id));
    // $ansers = pdo_getall("hello_banbanjia_ask_answer", array("uniacid" => $_W['uniacid'], 'aid' => $id),'*','id','desc id');
    if (!empty($ansers)) {
        foreach ($ansers as &$val) {
            $val['avatar'] = tomedia($val['avatar']);
            $val['addtime_cn'] = date("Y-m-d H:i", $val['addtime']);
        }
    }
    return $ansers;
}

function ask_get_informations($filter = array())
{
    global $_W;
    global $_GPC;
    if (empty($filter)) {
        $filter = $_GPC;
    }
    $condition = " where a.uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $agentid = intval($filter["agentid"]) ? intval($filter["agentid"]) : $_W["agentid"];
    if (!empty($agentid)) {
        $condition .= " and a.agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $cid = intval($filter["parentid"]);
    if (0 < $cid) {
        $condition .= " and a.parentid = :parentid";
        $params[":parentid"] = $cid;
    }
    $childid = intval($filter["childid"]);
    if (0 < $childid) {
        $condition .= " and a.childid = :childid";
        $params[":childid"] = $childid;
    }
    $uid = intval($filter["uid"]);
    if (0 < $uid) {
        $condition .= " and a.uid = :uid";
        $params[":uid"] = $uid;
    }
    $status = isset($filter["status"]) ? intval($filter["status"]) : 3;
    if (0 < $status) {
        $condition .= " and a.status = :status";
        $params[":status"] = $status;
    }

    $page = empty($filter["page"]) ? intval($_GPC["page"]) : intval($filter["page"]);
    $psize = empty($filter["psize"]) ? intval($_GPC["psize"]) : intval($filter["psize"]);
    $page = max(1, $page);
    $psize = $psize ? $psize : 10;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_ask_information") . " as a" . $condition, $params);
    $informations = pdo_fetchall("select a.*,b.nickname as ft_nickname,b.mobile as ft_mobile, b.avatar as ft_avatar from " . tablename("hello_banbanjia_ask_information") . " as a left join" . tablename("hello_banbanjia_members") . " as b on a.uid =b.uid" . $condition . " limit " . ($page - 1) * $psize . "," . $psize, $params);
    if (!empty($informations)) {
        if (empty($cid)) {
            $categorys = ask_get_categorys(array("type" => "all"), array("id", "title", "thumb"));
        }
        foreach ($informations as &$val) {
            if (!empty($val["thumbs"])) {
                $val["thumbs"] = iunserializer($val["thumbs"]);
                foreach ($val["thumbs"] as &$thumb) {
                    $thumb = tomedia($thumb);
                }
            }
            $cid = $val["childid"] ? $val["childid"] : $val["parentid"];
            $val["category"] = $categorys[$cid] ? $categorys[$cid] : $categorys;
            $val["addtime_cn"] = date("Y-m-d H:i", $val["addtime"]);
            $val["showall"] = false;
            $val["content_vue"] = nl2br($val["content"]);
            $val["content_length"] = istrlen($val["content"]);
            $br_length = substr_count($val["content_vue"], "<br />");
            if (2 < $br_length) {
                $val["content_length"] = 45;
            }
        }
    }
    $pager = pagination($total, $page, $psize);
    return array("informations" => $informations, "total" => $total, "pager" => $pager);
}

function ask_get_information($id, $filter = array())
{
    global $_W;
    global $_GPC;
    $condition = " where a.uniacid = :uniacid and a.id = :id";
    $params = array(":uniacid" => $_W["uniacid"], ":id" => $id);
    $information = pdo_fetch("select a.*, b.realname as ft_realname, b.mobile as ft_mobile, b.avatar as ft_avatar from " . tablename("hello_banbanjia_ask_information") . " as a left join" . tablename("hello_banbanjia_members") . " as b on a.uid =b.uid" . $condition, $params);
    if (!empty($information)) {
        if (!empty($information["thumbs"])) {
            $information["thumbs"] = iunserializer($information["thumbs"]);
            foreach ($information["thumbs"] as &$thumb) {
                $thumb = tomedia($thumb);
            }
        }
        $information["keyword"] = iunserializer($information["keyword"]);
        $cid = $information["childid"] ? $information["childid"] : $information["parentid"];
        $information["category"] = ask_get_category($cid);
        $information["addtime_cn"] = date("Y-m-d H:i", $information["addtime"]);
        // $information["status_all"] = ask_information_status($information["status"]);
        $information["content_share"] = $information["content"];
        $information["content"] = nl2br($information["content"]);
    }
    return $information;
}
