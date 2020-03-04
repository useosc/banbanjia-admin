<?php
error_reporting(E_ERROR);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
icheckauth(true);
if ($op == 'index') {
    if ($_W["ochannel"] == "wxapp" && $_W["is_agent"] && $_W["agentid"] == -1) {
        imessage(error(-2, "您所在的区域暂未获取到社区信息,建议您手动搜索地址或切换到此前常用的地址再试试"), "", "ajax");
    }
    article_cron();
    article_flow_update("falselooknum");
    mload()->lmodel("diy");
    if ($_config_wxapp["diy"]["use_diy_article"] != 1) {
        $pageOrid = get_wxapp_defaultpage("article");
        $config_share = $_config_plugin["share"];
        $share = array("title" => $config_share["title"], "desc" => $config_share["detail"], "link" => empty($config_share["link"]) ? ivurl("gohome/pages/article/index", array(), true) : $config_share["link"], "imgUrl" => tomedia($config_share["thumb"]));
    } else {
        $pageOrid = $_config_wxapp["diy"]["shopPage"]["article"];
        if (empty($pageOrid)) {
            imessage(error(-1, "未设置文章DIY页面"), "", "ajax");
        }
    }
    $page = get_wxapp_diy($pageOrid, true);
    if (empty($page)) {
        imessage(error(-1, "页面不能为空"), "", "ajax");
    }
    // $_W["_share"] = array("title" => $page["data"]["page"]["title"], "desc" => $page["data"]["page"]["desc"], "link" => ivurl("gohome/pages/article/index", array(), true), "imgUrl" => tomedia($page["data"]["page"]["thumb"]));
    if ($_config_wxapp["diy"]["use_diy_article"] != 1) {
        $_W["_share"] = $share;
    }
    $default_location = array();

    $result = array("diy" => $page);
    $_W['_nav'] = 1;
    imessage(error(0, $result), '', 'ajax');
} else {
    if ($op == 'information') { } else {
        if ($op == 'detail') {
            $id = intval($_GPC['id']);
            //浏览记录
            $footmark = pdo_get("hello_banbanjia_member_footmark", array("uniacid" => $_W['uniacid'], 'uid' => $_W['member']['uid'], "cid" => $id, 'type' => 'article', 'stat_day' => date("Ymd")), array("id"));
            if (empty($footmark)) {
                $insert = array("uniacid" => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'cid' => $id, 'type' => 'article', 'addtime' => TIMESTAMP, 'stat_day' => date("Ymd"));
                pdo_insert("hello_banbanjia_member_footmark", $insert);
            }

            gohome_update_activity_flow("article", $id, "looknum");
            $information = article_get_information($id, array("like_member_show" => 1));
            $comments = article_get_comments($id);
            // $_W[];
            $result = array("detail" => $information, "comments" => $comments);
            imessage(error(0, $result), "", "ajax");
        } else {
            if ($op == 'comment') {
                $id = intval($_GPC['id']);
                $update = array('uniacid' => $_W['uniacid'], 'agentid' => $_W['agentid'], 'aid' => $id, 'content' => trim($_GPC['content']), 'uid' => $_W['member']['uid'], 'nickname' => $_W['member']['nickname'], "avatar" => $_W["member"]["avatar"], "addtime" => TIMESTAMP);
                pdo_insert("hello_banbanjia_article_comment", $update);
                $extra = array("content" => $update["content"], "nickname" => $update["nickname"], "addtime" => $update["addtime"]);
                // article_wenzhang_notice($id, "comment", $extra);
                imessage(error(0, '评论成功'), '', 'ajax');
            } else {
                if ($op == 'reply') {
                    $id = intval($_GPC['id']);
                    $aid = intval($_GPC["aid"]);
                    $to_uid = intval($_GPC["to_uid"]);
                    $to_member = pdo_get("hello_banbanjia_members", array("uniacid" => $_W["uniacid"], "uid" => $to_uid), array("uid", "nickname", "avatar"));
                    $update = array("uniacid" => $_W["uniacid"], "aid" => $aid, "cid" => $id, "content" => trim($_GPC["content"]), "from_uid" => $_W["member"]["uid"], "from_nickname" => $_W["member"]["nickname"], "from_avatar" => $_W["member"]["avatar"], "to_uid" => $to_member["uid"], "to_nickname" => $to_member["nickname"], "to_avatar" => $to_member["avatar"], "addtime" => TIMESTAMP);
                    pdo_insert("hello_banbanjia_article_reply", $update);
                    $extra = array("content" => "回复:" . $update["content"], "nickname" => $update["from_nickname"], "addtime" => $update["addtime"]);
                    // article_wenzhang_notice($id, "reply", $extra);
                    imessage(error(0, "回复成功"), "", "ajax");
                } else {
                    if ($op == 'del') { } else {
                        if ($op == 'like') {
                            $id = intval($_GPC['id']);
                            $information = pdo_get("hello_banbanjia_article_information", array('uniacid' => $_W['uniacid'], 'id' => $id), array('id', 'likenum', 'like_uid'));
                            if (!empty($information)) {
                                $like_uid = iunserializer($information['like_uid']);
                                if (empty($like_uid)) {
                                    $like_uid = array();
                                }
                                if (in_array($_W['member']['uid'], $like_uid)) {
                                    imessage(error(-1, '您已赞过了'), '', 'ajax');
                                }
                                $like_uid[] = $_W['member']['uid'];
                            }
                            // article_flow_update("falselikenum");
                            $update = array('like_uid' => iserializer($like_uid), 'likenum' => $information['likenum'] + 1);
                            pdo_update("hello_banbanjia_article_information", $update, array("uniacid" => $_W['uniacid'], "id" => $id));
                            $extra = array("content" => '点赞', 'addtime' => TIMESTAMP, 'nickname' => $_W['member']['nickname']);
                            // article_wenzhang_notice($id,"like",$extra);
                            imessage(error(0, ''), '', 'ajax');
                        }
                    }
                }
            }
        }
    }
}
