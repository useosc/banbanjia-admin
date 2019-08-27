<?php
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL);
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
$op = trim($_GPC['op']) ? trim($_GPC['op']) : 'list';
if ($op == 'list') {
    $_W['page']['title'] = '自定义页面';
    $condition = " where uniacid = :uniacid and version = 2";
    $params = array(":uniacid" => $_W['uniacid']);
    $type = isset($_GPC["type"]) ? intval($_GPC["type"]) : -1;
    if (0 < $type) {
        $condition .= " and type = :type";
        $params["type"] = $type;
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and name like '%" . $keyword . "%'";
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_diypage") . $condition, $params);
    $pages = pdo_fetchall("select * from " . tablename("hello_banbanjia_diypage") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
    include itemplate('vue/diyPage');
} else {
    if ($op == 'post') {
        $_W['page']['title'] = '新建自定义页面';
        $id = intval($_GPC["id"]);
        $type = intval($_GPC["type"]);
        if ($type == 2) {
            $_W["page"]["title"] = "新建会员中心页面";
            $page["data"] = array("page" => array("type" => "2", "title" => "请输入页面标题", "name" => "会员中心", "desc" => "", "keyword" => "", "background" => "#F3F3F3", "diygotop" => "0", "navigationbackground" => "#000000", "navigationtextcolor" => "#ffffff"), "items" => array("M1528852766729" => array("params" => array("headerstyle" => "color", "avatarstyle" => "circle", "backgroundimgurl" => "", "explainurl" => "", "leftbtn" => array("text" => "充值", "linkurl" => ""), "rightbtn" => array("text" => "兑换", "linkurl" => "")), "style" => array("background" => "#FFD161", "color" => "#333333", "highlightcolor" => "#ff0000"), "id" => "memberHeader"), "M1528852802951" => array("params" => array("placeholdertext" => "如果您用手机号注册过会员或您想通过微信外购物请绑定您的手机号码"), "style" => array("margintop" => "10", "background" => "#ffffff", "titlecolor" => "#ff0011", "placeholdercolor" => "#999999"), "id" => "memberBindMobile"), "M1528853590433" => array("params" => array("navstyle" => "icon", "has_placeholder" => "0", "has_title" => "0", "title" => "我的资产"), "style" => array("margintop" => "10", "background" => "#ffffff", "titlecolor" => "#000000"), "data" => array("C1528853590433" => array("icon" => "icon-favor_light", "imgurl" => "../addons/hello_banbanjia/plugin/wxapp/static/img/default/navs-1.png", "linkurl" => "pages/member/favorite", "text" => "我的收藏", "textcolor" => "#000000", "placeholder" => "4个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853590434" => array("icon" => "icon-zuobiao", "imgurl" => "../addons/hello_banbanjia/plugin/wxapp/static/img/default/navs-2.png", "linkurl" => "pages/member/address", "text" => "我的地址", "textcolor" => "#000000", "placeholder" => "9个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44")), "id" => "blockNav"), "M1528853610330" => array("params" => array("navstyle" => "icon", "has_placeholder" => "1", "has_title" => "1", "title" => "我的资产"), "style" => array("margintop" => "10", "background" => "#ffffff", "titlecolor" => "#000000"), "data" => array("C1528853610330" => array("icon" => "icon-money", "imgurl" => "../addons/hello_banbanjia/plugin/wxapp/static/img/default/navs-1.png", "linkurl" => "pages/member/redPacket/index", "text" => "我的红包", "textcolor" => "#000000", "placeholder" => "暂无可用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853610331" => array("icon" => "icon-iconpiaoquan01", "imgurl" => "../addons/hello_banbanjia/plugin/wxapp/static/img/default/navs-2.png", "linkurl" => "pages/member/coupon/index", "text" => "我的代金券", "textcolor" => "#000000", "placeholder" => "暂无可用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853610332" => array("icon" => "icon-vipcard", "imgurl" => "../addons/hello_banbanjia/plugin/wxapp/static/img/default/navs-3.png", "linkurl" => "pages/deliveryCard/index", "text" => "配送会员卡", "textcolor" => "#000000", "placeholder" => "暂未购买", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44")), "id" => "blockNav"), "M1528853604178" => array("params" => array("navstyle" => "icon", "has_placeholder" => "0", "has_title" => "1", "title" => "我的服务"), "style" => array("margintop" => "10", "background" => "#ffffff", "titlecolor" => "#000000"), "data" => array("C1528853604180" => array("icon" => "icon-shop", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-3.png", "linkurl" => "pages/channel/brand", "text" => "为您优选", "textcolor" => "#000000", "placeholder" => "暂未购买", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "M1528854583730" => array("icon" => "icon-iconpiaoquan01", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-1.png", "linkurl" => "pages/channel/coupon", "text" => "领券中心", "textcolor" => "#000000", "placeholder" => "4个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "hot", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853604178" => array("icon" => "icon-creative", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-1.png", "linkurl" => "pages/home/help", "text" => "帮助与反馈", "textcolor" => "#000000", "placeholder" => "4个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853604179" => array("icon" => "icon-service", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-2.png", "linkurl" => "", "text" => "客服中心", "textcolor" => "#000000", "placeholder" => "9个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "", "markcolor" => "#ffffff", "markbackground" => "#FE4C44")), "id" => "blockNav"), "M1528853617410" => array("params" => array("navstyle" => "icon", "has_placeholder" => "0", "has_title" => "1", "title" => "更多推荐"), "style" => array("margintop" => "10", "background" => "#ffffff", "titlecolor" => "#000000"), "data" => array("C1528853617410" => array("icon" => "icon-store", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-1.png", "linkurl" => "pages/store/settle", "text" => "商家入驻", "textcolor" => "#000000", "placeholder" => "4个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "hot", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853617412" => array("icon" => "icon-friend", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-2.png", "linkurl" => "pages/spread/index", "text" => "啦啦推广", "textcolor" => "#000000", "placeholder" => "9个未使用", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "hot", "markcolor" => "#ffffff", "markbackground" => "#FE4C44"), "C1528853617413" => array("icon" => "icon-refund", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/navs-3.png", "linkurl" => "package/pages/ordergrant/index", "text" => "下单有礼", "textcolor" => "#000000", "placeholder" => "暂未购买", "placeholdercolor" => "#cccccc", "iconcolor" => "#676A6B", "marktext" => "hot", "markcolor" => "#ffffff", "markbackground" => "#FE4C44")), "id" => "blockNav"), "M1528857812641" => array("params" => array("content" => "请填写版权说明", "imgurl" => "../addons/we7_wmall/plugin/wxapp/static/img/default/copyright.png"), "style" => array("showimg" => 1, "style" => 1, "color" => "#CECECE", "background" => "#ffffff"), "max" => 1, "isbottom" => 1, "priority" => 2, "id" => "copyright")));
        }
        if (0 < $id) {
            $_W['page']['title'] = '编辑自定义页面';
        }
        // $diymenus = diypage_menu(2);
        // $activitys = store_all_activity();
        $plugins = get_available_plugin();

        include itemplate('vue/diyPage');
    }
}
