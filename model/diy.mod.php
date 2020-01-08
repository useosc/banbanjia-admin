<?php
echo "\r\n";
defined("IN_IA") or exit("Access Denied");
function get_wxapp_diy($pageOrid, $mobile = false, $extra = array())
{
    global $_W;
    if (is_array($pageOrid)) {
        $page = $pageOrid;
    } else {
        $id = intval($pageOrid);
        if (empty($id)) {
            return false;
        }
        $params = array('uniacid' => $_W['uniacid'], 'id' => $id, 'version' => 2);
        if (0 < $_W['agentid']) {
            $params['agentid'] = $_W['agentid'];
        }
        $page = pdo_get("hello_banbanjia_diypage", $params);
        if (empty($page)) {
            unset($params['agentid']);
            $page = pdo_get("hello_banbanjia_diypage", $params);
            if (empty($page) && $extra['pagepath'] == 'home') {
                $page = get_wxapp_defaultpage();
            }
        }
    }
    if (empty($page)) {
        return false;
    }
    $page['data'] = base64_decode($page['data']);
    $page['data'] = json_decode($page['data'], true);
    $page['parts'] = array();
    $page['cid'] = 0;
    $page['danmu'] = array();
    $page['is_show_kefu'] = 0;

    if (empty($page['data']['page']['title']) || strexists($page['data']['page']['title'], '搬搬家')) {
        $page['data']['page']['title'] = $_W['we7_hello_banbanjia']['config']['mall']['title'];
    }
    foreach ($page['data']['items'] as &$item) {
        $page['parts'][] = $item['id'];
        if ($item['id'] == 'fixedsearch') {
            $page['is_has_location'] = 1;
            if (!$item['params']['linkto']) {
                $item['params']['link'] = '/pages/home/search';
            } else {
                if ($item['params']['linkto'] == 1) {
                    $item['params']['link'] = '/gohome/pages/article/search';
                } else {
                    if ($item["params"]["linkto"] == 2) {
                        $item["params"]["link"] = "/gohome/pages/haodian/search";
                    }
                }
            }
            $page['fixedsearch'] = $item;
        } else {
            if ($item['id'] == 'guide') {
                $page['guide'] = $item;
                if (!isset($item["params"]["guidedata"])) {
                    $item["params"]["guidedata"] = 0;
                }
                if (empty($item["params"]["guidedata"])) {
                    if (!empty($item["data"])) {
                        foreach ($item["data"] as &$gvalue) {
                            $gvalue["imgUrl"] = tomedia($gvalue["imgUrl"]);
                        }
                    }
                } else {
                    if ($item['params']['guidedata'] == 1) {
                        $table = "hello_banbanjia_slide";
                        $keys = "id,title,thumb,link,displayorder";
                        $type = "startpage";
                    }
                    $condition = " where uniacid = :uniacid and type = :type and status = 1 ";
                    $params = array(":uniacid" => $_W["uniacid"], ":type" => $type);
                    if ($mobile || 0 < $_W["agentid"]) {
                        $condition .= " and agentid = :agentid ";
                        $params[":agentid"] = $_W["agentid"];
                    }
                    $slides = pdo_fetchall("select " . $keys . " from " . tablename($table) . $condition . " order by displayorder desc", $params);
                    $item["data"] = array();
                    if (!empty($slides)) {
                        foreach ($slides as $val) {
                            $childid = rand(1000000000, 9999999999.0);
                            $childid = "C" . $childid;
                            $item["data"][$childid] = array("pagePath" => $val["link"], "imgUrl" => tomedia($val["thumb"]));
                        }
                    }
                }
                if (empty($item["data"])) {
                    unset($page["guide"]);
                }
            }
        }
    }
    if (!$mobile) {
        if (!empty($page['data']['items']) && is_array($page['data']['items'])) {
            foreach ($page['data']['items'] as $itemid => &$item) {
                if ($item['id'] == 'notice') {
                    $item['data'] = get_wxapp_notice($item, false);
                    if (empty($item['data'])) {
                        unset($page['data']['items'][$itemid]);
                    }
                } else {
                    if ($item['id'] == 'navs') {
                        $result = get_wxapp_navs($item);
                        $item['data'] = $result['data'];
                        $item['data_num'] = $result['data_num'];
                        $item['row'] = $result['row'];
                        if (empty($item['data'])) {
                            unset($page['data']['items'][$itemid]);
                        }
                    } else {
                        if (!empty($page['data']['items']) && is_array($page['data']['items'])) {
                            foreach ($page['data']['items'] as $itemid => &$item) { }
                        }
                    }
                }
            }
            unset($item);
            pdo_update("hello_banbanjia_diypage", array("data" => base64_encode(json_encode($page["data"]))), array("uniacid" => $_W["uniacid"], "id" => $id));
        }
    } else {
        if (!empty($page["data"]["items"]) && is_array($page["data"]["items"])) {
            foreach ($page["data"]["items"] as $itemid => &$item) {
                if ($item["id"] == "article") {
                    mload()->lmodel("plugin");
                    pload()->model("article");
                    $infor_filter = array();
                    if ($item["params"]["informationdata"] != 1) {
                        $infor_filter["psize"] = $item["params"]["informationnum"];
                    }
                    $informations = article_get_informations($infor_filter);
                    $page["article"]["informationdata"] = $informations["informations"];
                    $page["article"]["has_get_all"] = !$item["params"]["informationdata"];
                } else {
                    if ($item['id'] == 'memberHeader') {
                        $item['member'] = $_W['member'];
                        if ($item['params']['headerstyle'] == 'img') {
                            $item['params']['backgroundimgurl'] = tomedia($item["params"]["backgroundimgurl"]);
                        }
                    } else {
                        if ($item['id'] == 'blockNav') {
                            if (!empty($item['data'])) {
                                foreach ($item['data'] as &$value) {
                                    $value["imgurl"] = tomedia($value["imgurl"]);
                                }
                            }
                        } else {
                            if ($item['id'] == 'picture') {
                                $result = get_wxapp_slides($item, true);
                                $item['data'] = array_values($result['data']);
                                if (empty($item["data"])) {
                                    unset($page["data"]["items"][$itemid]);
                                }
                            }
                        }
                    }
                }
            }
            unset($item);
        }
    }
    return $page;
}

function get_wxapp_pages($filter = array(), $search = array("*"))
{
    global $_W;
    $condition = " where uniacid = :uniacid and agentid = :agentid";
    $params = array(":uniacid" => $_W['uniacid'], ":agentid" => $_W['agentid'] || 0);
    $table = "hello_banbanjia_diypage";
    if ($filter['form'] == 'wap') {
        $condition .= " and `version` = :version";
        $params[":version"] = 2;
    }
    if (!empty($filter) && !empty($filter['type'])) {
        $condition .= " and type = :type";
        $params[":type"] = intval($filter['type']);
    }
    if (!empty($search)) {
        $search = implode(",", $search);
    }
    $pages = pdo_fetchall("select " . $search . " from " . tablename($table) . $condition, $params);
    return $pages;
}

function get_wxapp_danmu($config_danmu = array())
{
    global $_W;
    if (empty($config_danmu)) {
        $config_danmu = get_plugin_config("diypage.danmu");
    }
    if (!is_array($config_danmu) || !$config_danmu['params']['status']) {
        return error(-1, '');
    }
    if ($config_danmu['params']['dataType'] == 1) {
        $members = pdo_fetchall("select b.nickname,b.avatar from " . tablename("hello_banbanjia_order") . " as a left join " . tablename("hello_banbanjia_members") . " as b on a.uid = b.uid where a.uniacid = :uniacid and a.agentid = :agentid and b.nickname != '' and b.avatar != '' order by a.id desc limit 10;", array(':uniacid' => $_W['uniacid'], ':agentid' => $_W['agentid']));
    }
    if (empty($members)) {
        $members = pdo_fetchall("select nickname, avatar from " . tablename("hello_banbanjia_members") . " where uniacid = :uniacid and nickname != '' and avatar != '' order by id desc limit 10;", array(":uniacid" => $_W["uniacid"]));
    }
    if (!empty($members)) {
        foreach ($members as &$val) {
            $val['avatar'] = tomedia($val['avatar']);
            $val['time'] = mt_rand($config_danmu['params']['starttime'], $config_danmu['params']['endtime']);
            if ($val['time'] <= 0) {
                $val['time'] = '刚刚';
            } else {
                if (0 < $val['time'] && $val['time'] < 60) {
                    $val['time'] = (string) $val['time'] . "秒前";
                } else {
                    if (60 < $val["time"]) {
                        $val["time"] = floor($val["time"] / 60);
                        $val["time"] = (string) $val["time"] . "分钟前";
                    }
                }
            }
        }
    }
    $config_danmu['members'] = $members;
    return $config_danmu;
}

function get_wxapp_notice($item, $mobile = false, $from = 'wxapp')
{
    global $_W;
    if ($item['params']['noticedata'] == 0 || $item["params"]["noticedata"] == 2) { //社区和平台
        if ($item['params']['noticedata'] == 0) {
            $table = "hello_banbanjia_notice";
            $keys = "id,title,displayorder,link,status,wxapp_link";
        } else {
            if ($item['params']['noticedata'] == 2) {
                $table = "hello_banbanjia_gohome_notice";
                $keys = "id,title,displayorder,status,wxapp_link";
            }
        }
        $condition = " where uniacid = :uniacid";
        $params = array(":uniacid" => $_W['uniacid']);
        if ($item['params']['noticedata'] == 0) {
            $condition .= " and type = :type";
            $params[":type"] = "member";
        }
        if ($mobile || 0 < $_W["agentid"]) {
            $condition .= " and agentid = :agentid";
            $params[":agentid"] = $_W["agentid"];
        }
        $noticenum = $item["params"]["noticenum"];
        $notice = pdo_fetchall("select " . $keys . "from " . tablename($table) . $condition . " and status = 1 order by displayorder desc limit " . $noticenum, $params);
        $item['data'] = array();
        if (!empty($notice)) {
            foreach ($notice as &$data) {
                $childid = rand(1000000000, 9999999999.0);
                $childid = "C" . $childid;
                $item["data"][$childid] = array("id" => $data["id"], "title" => $data["title"], "linkurl" => $data["wxapp_link"]);
            }
        }
    }
    return $item['data'];
}

function get_wxapp_navs($item, $mobile = false)
{
    global $_W;
    if ($item['params']['navsdata'] == 0) {
        if (!empty($item['data'])) {
            foreach ($item['data'] as &$val) {
                $val['imgurl'] = tomedia($val['imgurl']);
            }
        }
    } else { }
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W['uniacid']);
    if ($mobile || 0 < $_W['agentid']) {
        $condition .= " and agentid = :agentid";
        $params[":agentid"] = $_W['agentid'];
    }
    if (in_array($item['params']['navsdata'], array(1, 3, 4))) {
        $condition .= " and parentid = 0";
    }
    // $limit = intval($item['params']['navsnum']) ? intval($item['params']['navsnum']) : 4;
    // $navs = pdo_fetchall("select " . $keys . " from " . tablename($table) . $condition . " and status = 1 order by displayorder desc limit " . $limit, $params);

    return $result;
}

function get_wxapp_slides($item,$mobile = false){
    global $_W;
    if (empty($item["params"]["picturedata"])) {
        if (!empty($item["data"])) {
            foreach ($item["data"] as &$val) {
                $val["imgurl"] = tomedia($val["imgurl"]);
            }
        }
    }else{
        if ($item["params"]["picturedata"] == 1) {
            $table = "hello_banbanjia_slide";
            $keys = "id,title,thumb,wxapp_link,link,displayorder";
            $type = "homeTop";
        }
        $condition = " where uniacid = :uniacid and type = :type and status = 1 ";
        $params = array(":uniacid" => $_W["uniacid"], ":type" => $type);
        // if ($mobile || 0 < $_W["agentid"]) {
        //     $condition .= " and agentid = :agentid ";
        //     $params[":agentid"] = $_W["agentid"];
        // }
        $slides = pdo_fetchall("select " . $keys . " from " . tablename($table) . $condition . " order by displayorder desc", $params);
        $item["data"] = array();
        if (!empty($slides)) {
            foreach ($slides as $val) {
                $childid = rand(1000000000, 9999999999.0);
                $childid = "C" . $childid;
                $item["data"][$childid] = array("linkurl" => empty($val["wxapp_link"]) ? $val["link"] : $val["wxapp_link"], "imgurl" => tomedia($val["thumb"]));
            }
        }
    }
    $result = array("data" => $item["data"]);
    return $result;
}