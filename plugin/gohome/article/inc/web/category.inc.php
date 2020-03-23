<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_W["page"]["title"] = "分类列表";
    $condition = " where uniacid = :uniacid and parentid = 0";
    $params = array(":uniacid" => $_W['uniacid']);
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_article_category") . $condition, $params);
    $category = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_article_category") . $condition . " ORDER BY displayorder DESC,id ASC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params, "id");
    if (!empty($category)) {
        foreach ($category as $key => &$val) {
            $val['child'] = pdo_fetchall("select * from" . tablename("hello_banbanjia_article_category") . "where uniacid = :uniacid and parentid = :parentid order by displayorder desc,id asc", array(":uniacid" => $_W['uniacid'], ":parentid" => $key));
        }
    }
    $pager = pagination($total, $pindex, $psize);
    include itemplate('category');
    return 1;
} else {
    if ($op == 'post') {
        $_W['page']['title'] = '编辑分类';
        $id = intval($_GPC['id']);
        if (0 < $id) {
            $category = pdo_get('hello_banbanjia_article_category', array("uniacid" => $_W['uniacid'], 'id' => $id));
            $category['tags'] = iunserializer($category['tags']);
            $category["tags"] = implode("\n", $category["tags"]);
            $category["config"] = iunserializer($category["config"]);
        }
        if ($_W['ispost']) {
            $data = array('uniacid' => intval($_W['uniacid']), 'displayorder' => intval($_GPC['displayorder']), 'title' => trim($_GPC['title']), 'content' => trim($_GPC['content']), 'thumb' => trim($_GPC['thumb']), 'price' => floatval($_GPC['price']), 'status' => intval($_GPC['status']), 'is_hot' => intval($_GPC['is_hot']), 'link' => trim($_GPC['link']), 'tags' => explode("\n", trim($_GPC['tags'])));
            $data["tags"] = array_filter($data["tags"], trim);
            $data["tags"] = iserializer($data["tags"]);
            $data["config"] = array();
            if (!empty($_GPC["config"]["orderby"])) {
                $data["config"] = array("orderby" => trim($_GPC["config"]["orderby"]));
            }
            if (!empty($_GPC["config"]["stick_price"])) {
                foreach ($_GPC["config"]["stick_price"]["day"] as $key => $val) {
                    $val = trim($val);
                    if (empty($val)) {
                        continue;
                    }
                    $price = $_GPC["config"]["stick_price"]["price"][$key];
                    if (empty($price)) {
                        continue;
                    }
                    $stick_price[$val] = array("day" => $val, "price" => $price);
                }
                $data["config"]["stick_price"] = $stick_price;
            }
            $data["config"] = iserializer($data["config"]);
            if (empty($_GPC["id"])) {
                if (!empty($_GPC["parentid"])) {
                    $data["parentid"] = intval($_GPC["parentid"]);
                    // $data["agentid"] = intval(pdo_fetchcolumn("select agentid from " . tablename("hello_banbanjia_article_category") . " where uniacid = :uniacid and id = :id", array(":uniacid" => $_W["uniacid"], ":id" => $data["parentid"])));
                    pdo_insert("hello_banbanjia_article_category", $data);
                } else {
                    pdo_insert("hello_banbanjia_article_category", $data);
                }
            } else {
                pdo_update("hello_banbanjia_article_category", $data, array("uniacid" => $_W["uniacid"], "id" => $_GPC["id"]));
            }
            imessage(error(0, "编辑分类成功"), iurl("article/category/list"), "ajax");
        }
        include itemplate('category');
        return 1;
    }else{
        if ($op == "del") {
            $id = intval($_GPC["id"]);
            pdo_delete("hello_banbanjia_article_category", array("uniacid" => $_W["uniacid"], "id" => $id));
            imessage(error(0, "删除分类成功"), iurl("article/category/list"), "ajax");
        }
    }
}
