<?php 
defined("IN_IA") or exit( "Access Denied" );
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "评论列表";
    $condition = " where a.uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $agentid = intval($_GPC["agentid"]);
    if (0 < $agentid) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $agentid;
    }
    $aid = intval($_GPC["aid"]);
    if (0 < $tid) {
        $condition .= " and a.aid = :aid";
        $params[":aid"] = $aid;
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and (a.content like :keyword or b.content like :keyword)";
        $params[":keyword"] = "%" . $keyword . "%";
    }
    if (!empty($_GPC["addtime"])) {
        $starttime = strtotime($_GPC["addtime"]["start"]);
        $endtime = strtotime($_GPC["addtime"]["end"]) + 86399;
    } else {
        $starttime = strtotime("-7 day");
        $endtime = TIMESTAMP;
    }
    if (!empty($starttime) && !empty($endtime)) {
        $condition .= " and a.addtime > :start AND a.addtime < :end";
        $params[":start"] = $starttime;
        $params[":end"] = $endtime;
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 10;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_article_comment") . " as a left join " . tablename("hello_banbanjia_article_information") . "as b on a.aid = b.id " . $condition, $params);
    $comments = pdo_fetchall("select a.*, b.content as wenzhang_content from " . tablename("hello_banbanjia_article_comment") . " as a left join" . tablename("hello_banbanjia_article_information") . " as b on a.aid = b.id" . $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    if (!empty($comments)) {
        foreach ($comments as &$val) {
            $val["reply"] = pdo_fetchall("select * from " . tablename("hello_banbanjia_article_reply") . " where aid = " . $val["aid"] . " and cid = " . $val["id"]);
        }
    }
    $pager = pagination($total, $pindex, $psize);
} else {
    if ($op == "delete") {
        $id = intval($_GPC["id"]);
        if (0 < $id) {
            pdo_delete("hello_banbanjia_article_comment", array("uniacid" => $_W["uniacid"], "id" => $id));
            $reply = pdo_getall("hello_banbanjia_article_reply", array("uniacid" => $_W["uniacid"], "cid" => $id));
            if (!empty($reply)) {
                pdo_delete("hello_banbanjia_article_reply", array("uniacid" => $_W["uniacid"], "cid" => $id));
            }

            imessage(error(0, "删除评论成功"), "", "ajax");
        }
    } else {
        if ($op == "del") {
            $id = intval($_GPC["id"]);
            if (0 < $id) {
                pdo_delete("hello_banbanjia_article_reply", array("uniacid" => $_W["uniacid"], "id" => $id));
                imessage(error(0, "删除回复成功"), "", "ajax");
            }
        }
    }
}
include itemplate("comment");

?>