<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "文章列表";
    $is_stick = isset($_GPC['is_stick']) ? intval($_GPC['is_stick']) : '-1';
    if (!empty($_GPC["addtime"])) {
        $starttime = strtotime($_GPC["addtime"]["start"]);
        $endtime = strtotime($_GPC["addtime"]["end"]) + 86399;
    } else {
        $starttime = strtotime("-7 day");
        $endtime = TIMESTAMP;
    }
    $_GPC["starttime"] = $starttime;
    $_GPC["endtime"] = $endtime;
    if (!empty($_GPC["cid"])) {
        $cid = $_GPC["cid"];
        if (strexists($cid, ":")) {
            $cid = explode(":", $cid);
            $_GPC["parentid"] = intval($cid[0]);
            $_GPC["childid"] = intval($cid[1]);
        } else {
            $_GPC["parentid"] = intval($cid);
        }
    }
    $filter = $_GPC;
    $filter["orderby"] = "addtime";
    $filter["psize"] = 20;
    $filter["status"] = isset($filter["status"]) ? intval($filter["status"]) : -1;
    $informations = article_get_informations($filter);
    $information = $informations["informations"];
    $pager = $informations["pager"];
    $categorys = article_get_categorys();
} else {
    if ($op == "detail") {
        $_W["page"]["title"] = "文章详情";
        article_cron();
        $id = intval($_GPC["id"]);
        $information = article_get_information($id);
        $status = $information["status"];
        $categorys = article_get_categorys(array("type" => "parent&child"), array("id", "title", "parentid"));
        if ($_W["ispost"]) {
            $data = array("title" => trim($_GPC["title"]), "content" => trim($_GPC["content"]), "looknum" => intval($_GPC["looknum"]), "likenum" => intval($_GPC["likenum"]), "sharenum" => intval($_GPC["sharenum"]), "is_stick" => intval($_GPC["is_stick"]), "status" => intval($_GPC["status"]), "parentid" => intval($_GPC["category"]["parentid"]), "childid" => intval($_GPC["category"]["childid"]));
            $data["thumbs"] = array();
            if (!empty($_GPC["thumbs"])) {
                foreach ($_GPC["thumbs"] as $thumb) {
                    if (empty($thumb)) {
                        continue;
                    }
                    $data["thumbs"][] = $thumb;
                }
            }
            $data["thumbs"] = iserializer($data["thumbs"]);
            if ($data["is_stick"] == "1") {
                $overtime = trim($_GPC["overtime"]);
                $data["overtime"] = strtotime($overtime);
            }
            pdo_update("hello_banbanjia_article_information", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
            imessage(error(0, "编辑文章成功"), iurl("article/information/detail", array("id" => $id)), "ajax");
        }
    } else {
        if ($op == 'del') {
            $ids = $_GPC['id'];
            $result = article_information_delete($ids);
            imessage($result, '', 'ajax');
        } else {
            if ($op == 'status') {
                $status = intval($_GPC["status"]);
                $ids = $_GPC["id"];
                $result = article_information_update_status($ids, $status);
                imessage($result, referer(), "ajax");
            }else{
                if($op == "toblack"){
                    mload()->lmodel("member.extra");
                    $uid = intval($_GPC["uid"]);
                    $status = member_to_black($uid, "article");
                    if ($status) {
                        imessage(error(0, "加入黑名单成功"), referer(), "ajax");
                    } else {
                        imessage(error(-1, "加入黑名单失败"), referer(), "ajax");
                    }
                }
            }
        }
    }
}
include(itemplate("information"));
