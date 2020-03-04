<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
icheckauth();
if ($ta == 'list') {
    pload()->model('ask');
    pload()->model('article');
    $asks = ask_get_informations();
    $articles_inf = article_get_informations();
    $articles = $articles_inf['informations'];
    // var_dump($articles);exit;
    $time  = TIMESTAMP - 7776000;
    pdo_query("delete from " . tablename("hello_banbanjia_member_footmark") . " where uniacid = :uniacid and addtime < :time", array(":uniacid" => $_W['uniacid'], ":time" => $time));
    $stores = pdo_fetchall("select id,score,title,logo,click from" . tablename('hello_banbanjia_store') . " where uniacid = :uniacid", array(":uniacid" => $_W['uniacid']), 'id');
    // $asks = pdo_fetchall("");
    // $articles = pdo_fetchall();

    $footmarks = pdo_fetchall("select * from " . tablename("hello_banbanjia_member_footmark") . " where uniacid = :uniacid and uid = :uid group by stat_day order by stat_day desc", array(":uniacid" => $_W['uniacid'], ":uid" => $_W['member']['uid']));
    if (!empty($footmarks)) {
        foreach ($footmarks as &$val) {
            $val['date'] = date("m-d", $val['addtime']);
            if ($val['stat_day'] == date("Ymd")) {
                $val['date'] = '今天';
            } else {
                if ($val['stat_day'] == date("Ymd") - 1) {
                    $val['date'] = '昨天';
                }
            }
            $val['marks'] = pdo_getall("hello_banbanjia_member_footmark", array("uniacid" => $_W['uniacid'], 'uid' => $_W['member']['uid'], 'stat_day' => $val['stat_day']), array("id", "cid", "type"));
            foreach ($val['marks'] as $mark) {
                if ($mark['type'] == 'store') {
                    if (!empty($stores[$mark['cid']])) {
                        $val["stores"][] = $stores[$mark["cid"]];
                    }
                } elseif ($mark['type'] == 'article') {
                    foreach($articles as $article) {
                        if($article['id'] == $mark['cid']){
                            $val['articles'][] = $article;
                        }
                    }
                } elseif ($mark['type'] == 'ask') {
                    if (!empty($asks[$mark['cid']])) {
                        $val['asks'][] = $asks[$mark['cid']];
                    }
                }
            }
        }
    }
    $result = array("footmarks" => $footmarks);
    imessage(error(0, $result), '', 'ajax');
}
if ($ta == 'del') { }
