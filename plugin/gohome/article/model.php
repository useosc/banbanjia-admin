<?php
defined("IN_IA") or exit("Access Denied");
pload()->model("gohome");
function article_get_categorys($filter = array(), $field = array())
{
    global $_W;
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $agentid = intval($filter["agentid"]) ? intval($filter["agentid"]) : $_W["agentid"];
    if (!empty($agentid)) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $status = isset($filter['status']) ? intval($filter['status']) : -1;
    if (-1 < $status) {
        $condition .= " and status = :status";
        $params[":status"] = $status;
    }
    $type = empty($filter['type']) ? 'parent_child' : trim($filter['type']);
    if ($type == "parent") {
        $condition .= " and parentid = 0";
    }
    $field_str = "*";
    if (!empty($field)) {
        $field_str =  implode(',', $field);
    }
    $categorys = pdo_fetchall("select " . $field_str . " from " . tablename('hello_banbanjia_article_category') . $condition . " order by displayorder desc", $params, 'id');
    if (!empty($categorys)) {
        foreach ($categorys as &$val) {
            if (isset($val['thumb'])) {
                $val['thumb'] = tomedia($val['thumb']);
            }
            if (isset($val['tags'])) {
                $val['tags'] = iunserializer($val['tags']);
            }
            if (empty($val["link"])) {
                $val["link"] = "/gohome/pages/article/category?id=" . $val["id"];
            }
            if (isset($val["config"])) {
                $val["config"] = iunserializer($val["config"]);
            }
            if ($type == 'parent_child') {
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
function article_get_category($id, $field = array())
{
    global $_W;
    $condition = " where uniacid = :uniacid and id = :id";
    $params = array(":uniacid" => $_W["uniacid"], ":id" => $id);
    $field_str = "*";
    if (!empty($field)) {
        $field_str = implode(",", $field);
    }
    $category = pdo_fetch("select " . $field_str . " from " . tablename("hello_banbanjia_article_category") . $condition, $params);
    if (!empty($category)) {
        $category["thumb"] = tomedia($category["thumb"]);
        $category["tags"] = iunserializer($category["tags"]);
        if (empty($category["link"])) {
            $category["link"] = "/gohome/pages/article/category?id=" . $category["id"];
        }
        if (isset($category["config"])) {
            $category["config"] = iunserializer($category["config"]);
        }
    }
    return $category;
}

function article_can_publish_information()
{
    global $_W;
    $config_article = $_W['_plugin']['config']['article'];
    $total_limit = $config_article["limit_num"]["total_num"];
    $day_limit = $config_article["limit_num"]["day_num"];
    if (empty($total_limit) && empty($day_limit)) {
        return true;
    }
    $condition = " where uniacid = :uniacid and agentid = :agentid and uid = :uid and status = 3";
    $params = array(":uniacid" => $_W["uniacid"], ":agentid" => $_W["agentid"], ":uid" => $_W["member"]["uid"]);
    if (0 < $day_limit) {
        $condition .= " and addtime > :starttime and addtime < :endtime";
        $params[":starttime"] = strtotime(date("Y-m-d", TIMESTAMP));
        $params[":endtime"] = $params[":starttime"] + 86399;
        $day_num = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_article_information") . $condition, $params);
        if ($day_limit <= $day_num) {
            return error(-1, "今日发文章已超过最大限制");
        }
    }
    if (0 < $total_limit) {
        $total_num = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_article_information") . $condition, $params);
        if ($total_limit <= $total_num) {
            return error(-1, "发文章已超过最大限制，请删除无用文章");
        }
    }
    return true;
}
function article_information_publish_calculate($categoryOrId, $condition)
{
    global $_W;
    $category = $categoryOrId;
    if (!is_array($category)) {
        $category = pdo_get("hello_banbanjia_article_category", array("uniacid" => $_W["uniacid"], "id" => $category));
    }
    if (!empty($category)) {
        $price = floatval($category["price"]);
        if (0 < $condition["information_id"]) {
            $price = 0;
        }
        $stick_is_available = 0;
        $category["config"] = iunserializer($category["config"]);
        if (!empty($category["config"]["stick_price"])) {
            $stick_num_limit = $_W["_plugin"]["config"]["article"]["stick_num"];
            if (0 < $stick_num_limit) {
                $stick_num = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_article_information") . " where uniacid = :uniacid and is_stick = 1", array(":uniacid" => $_W["uniacid"]));
                if ($stick_num < $stick_num_limit) {
                    $stick_is_available = 1;
                }
            } else {
                $stick_is_available = 1;
            }
        }
        $stick_fee = 0;
        if ($stick_is_available == 1) {
            $day = intval($condition["days"]);
            if (0 < $day) {
                $stick_fee = floatval($category["config"]["stick_price"][$day]["price"]);
            }
        }
        $result = array("price" => $price, "stick_price" => $stick_fee, "is_stick" => 0 < $stick_fee ? 1 : 0, "days" => $day, "stick_is_available" => $stick_is_available);
        $result["final_fee"] = $result["price"] + $result["stick_price"];
        return $result;
    }
    return false;
}
function article_information_stick_sync()
{
    global $_W;
    pdo_query("update " . tablename("hello_banbanjia_article_information") . " set is_stick = 0 where uniacid = :uniacid and is_stick =1 and overtime < :endtime", array(":uniacid" => $_W['uniaicd'], ":endtime" => TIMESTAMP));
    return true;
}
function article_cron()
{
    global $_W;
    $key = "we7_hello_banbanjia:" . $_W["uniacid"] . ":article:lock:120";
    if (check_cache_status($key, 120)) {
        return true;
    }
    article_information_stick_sync();
    set_cache($key, array());
    return true;
}
function article_flow_update($type = '')
{
    global $_W;
    $config = $_W["_plugin"]["config"]["article"];
    if (empty($config)) {
        if (0 < $_W["agentid"]) {
            $config = get_agent_plugin_config("gohome.article");
        } else {
            $config = get_plugin_config("gohome.article");
        }
    }
    if (in_array($type, array("falselooknum", "falsefabunum", "falselikenum"))) {
        $add_num = 1;
        if ($type == "falselooknum" && 0 < $config["minup"] && $config["minup"] <= $config["maxup"]) {
            $add_num = rand($config["minup"], $config["maxup"]);
        }
        $config[$type] = $config[$type] + $add_num;
        if (0 < $_W["agentid"]) {
            set_agent_plugin_config("gohome.article", $config);
        } else {
            set_plugin_config("gohome.article", $config);
        }
    }
    return array("falselooknum" => intval($config["falselooknum"]), "falsefabunum" => intval($config["falsefabunum"]), "falselikenum" => intval($config["falselikenum"]));
}
function article_get_information($id, $filter = array())
{
    global $_W;
    global $_GPC;
    $condition = " where a.uniacid = :uniacid and a.id = :id";
    $params = array(":uniacid" => $_W["uniacid"], ":id" => $id);
    $information = pdo_fetch("select a.*, b.realname as ft_realname, b.mobile as ft_mobile, b.avatar as ft_avatar from " . tablename("hello_banbanjia_article_information") . " as a left join" . tablename("hello_banbanjia_members") . " as b on a.uid =b.uid" . $condition, $params);
    if(!empty($information)) {
        if (!empty($information["thumbs"])) {
            $information["thumbs"] = iunserializer($information["thumbs"]);
            foreach ($information["thumbs"] as &$thumb) {
                $thumb = tomedia($thumb);
            }
        }
        if ($filter["like_member_show"] == 1) {
            $information["like_uid"] = iunserializer($information["like_uid"]);
            if (!empty($information["like_uid"])) {
                $like_uids = implode(",", $information["like_uid"]);
                $like_members = pdo_fetchall("select avatar from" . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and uid in (" . $like_uids . ")", array(":uniacid" => $_W["uniacid"]));
                foreach ($like_members as $avatar) {
                    $information["like_avatar"][] = tomedia($avatar["avatar"]);
                }
            }
        }
        $information["keyword"] = iunserializer($information["keyword"]);
        $cid = $information["childid"] ? $information["childid"] : $information["parentid"];
        $information["category"] = article_get_category($cid);
        $information["addtime_cn"] = date("Y-m-d H:i", $information["addtime"]);
        $information["status_all"] = article_information_status($information["status"]);
        $information["content_share"] = $information["content"];
        $information["content"] = nl2br($information["content"]);
    }
    return $information;
}
function article_get_comments($id)
{
    global $_W;
    $comments = pdo_getall("hello_banbanjia_article_comment", array("uniacid" => $_W['uniacid'], 'aid' => $id));
    if (!empty($comments)) {
        foreach ($comments as &$val) {
            $val['avatar'] = tomedia($val['avatar']);
            $val['addtime_cn'] = date("Y-m-d H:i", $val['addtime']);
            $replys = pdo_getall("hello_banbanjia_article_reply", array('uniacid' => $_W['uniacid'], 'cid' => $val['id']));
            if (!empty($replys)) {
                foreach ($replys as &$v) {
                    $v['from_avatar'] = tomedia($v['from_avatar']);
                    $v["to_avatar"] = tomedia($v["to_avatar"]);
                    $v["addtime_cn"] = date("Y-m-d H:i", $v["addtime"]);
                }
            }
            $val["reply"] = $replys;
        }
    }
    return $comments;
}
function article_get_informations($filter = array())
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
    $is_stick = isset($filter["is_stick"]) ? intval($filter["is_stick"]) : "-1";
    if (-1 < $is_stick) {
        $condition .= " and a.is_stick = :is_stick";
        $params[":is_stick"] = $is_stick;
    }
    $keyword = trim($filter["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and (a.mobile like :keyword or a.nickname like :keyword or a.content like :keyword)";
        $params[":keyword"] = "%" . $keyword . "%";
    }
    if (!empty($filter["starttime"]) && !empty($filter["endtime"])) {
        $condition .= " AND a.addtime > :start AND a.addtime < :end";
        $params[":start"] = $filter["starttime"];
        $params[":end"] = $filter["endtime"];
    }
    $orderby = " order by a.is_stick desc, a.id desc";
    if (0 < $cid) {
        $categorys = article_get_category($cid, array("id", "title", "thumb", "config"));
        if ($filter["orderby"] != "addtime" && in_array($categorys["config"]["orderby"], array("looknum", "likenum", "sharenum"))) {
            $orderby = " order by a.is_stick desc, a." . $categorys["config"]["orderby"] . " desc, a.id desc";
        }
    }
    $page = empty($filter["page"]) ? intval($_GPC["page"]) : intval($filter["page"]);
    $psize = empty($filter["psize"]) ? intval($_GPC["psize"]) : intval($filter["psize"]);
    $page = max(1, $page);
    $psize = $psize ? $psize : 10;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_article_information") . " as a" . $condition, $params);
    $informations = pdo_fetchall("select a.*,b.realname as ft_readlname,b.mobile as ft_mobile, b.avatar as ft_avatar from " . tablename("hello_banbanjia_article_information") . " as a left join" . tablename("hello_banbanjia_members") . " as b on a.uid =b.uid" . $condition . $orderby . " limit " . ($page - 1) * $psize . "," . $psize, $params);
    if (!empty($informations)) {
        if (empty($cid)) {
            $categorys = article_get_categorys(array("type" => "all"), array("id", "title", "thumb"));
        }
        $all_status = article_information_status();
        foreach ($informations as &$val) {
            if (!empty($val["thumbs"])) {
                $val["thumbs"] = iunserializer($val["thumbs"]);
                foreach ($val["thumbs"] as &$thumb) {
                    $thumb = tomedia($thumb);
                }
            }
            $val["keyword"] = iunserializer($val["keyword"]);
            $cid = $val["childid"] ? $val["childid"] : $val["parentid"];
            $val["category"] = $categorys[$cid] ? $categorys[$cid] : $categorys;
            $val["addtime_cn"] = date("Y-m-d H:i", $val["addtime"]);
            $val["status_all"] = $all_status[$val["status"]];
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

function article_information_status($type = "", $key = "all")
{
    $data = array("1" => array("text" => "待付款", "css" => "label label-warning"), "2" => array("text" => "待审核", "css" => "label label-warning"), "3" => array("text" => "显示中", "css" => "label label-success"), "4" => array("text" => "未通过", "css" => "label label-danger"));
    if (empty($type)) {
        return $data;
    }
    if ($key == "all") {
        return $data[$type];
    }
    if ($key == "text") {
        return $data[$type]["text"];
    }
    if ($key == "css") {
        return $data[$type]["css"];
    }
}
function article_information_delete($ids, $type = "information")
{
    global $_W;
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        if ($type == "information") {
            pdo_delete("hello_banbanjia_article_information", array("uniacid" => $_W["uniacid"], "id" => $id));
            pdo_delete("hello_banbanjia_article_comment", array("uniacid" => $_W["uniacid"], "tid" => $id));
            pdo_delete("hello_banbanjia_article_reply", array("uniacid" => $_W["uniacid"], "tid" => $id));
        }
    }
    return error(0, "删除成功");
}
function article_information_update_status($ids, $status)
{
    global $_W;
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_update("hello_banbanjia_article_information", array("status" => intval($status)), array("uniacid" => $_W["uniacid"], "id" => intval($id)));
    }
    return error(0, "设置成功");
}
