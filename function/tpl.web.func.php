<?php

defined("IN_IA") or exit("Access Denied");

function tpl_form_field_fans($name, $value, $scene = "notify", $required = false)
{
    global $_W;
    if (empty($default)) {
        $default = "./resource/images/nopic.jpg";
    }
    $s = "";
    if (!defined("TPL_INIT_HELLO_FANS")) {
        $option = array("scene" => $scene);
        $option = json_encode($option);
        $s = "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tfunction showFansDialog(elm) {\r\n\t\t\t\tvar btn = \$(elm);\r\n\t\t\t\tvar openid_wxapp = btn.parent().prev();\r\n\t\t\t\tvar openid = btn.parent().prev().prev();\r\n\t\t\t\tvar avatar = btn.parent().prev().prev().prev();\r\n\t\t\t\tvar nickname = btn.parent().prev().prev().prev().prev();\r\n\t\t\t\tvar img = btn.parent().parent().next().find(\"img\");\r\n\t\t\t\tirequire([\"web/hello\"], function(hello){\r\n\t\t\t\t\thello.selectfan(function(fans){\r\n\t\t\t\t\t\tconsole.log(fans);\r\n\t\t\t\t\t\tif(img.length > 0){\r\n\t\t\t\t\t\t\timg.get(0).src = fans.avatar;\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\topenid_wxapp.val(fans.openid_wxapp);\r\n\t\t\t\t\t\topenid.val(fans.openid);\r\n\t\t\t\t\t\tavatar.val(fans.avatar);\r\n\t\t\t\t\t\tnickname.val(fans.nickname);\r\n\t\t\t\t\t}, " . $option . ");\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t</script>";
        define("TPL_INIT_HELLO_FANS", true);
    }
    $s .= "\r\n\t\t<div class=\"input-group\">\r\n\t\t\t<input type=\"text\" name=\"" . $name . "[nickname]\" value=\"" . $value["nickname"] . "\" class=\"form-control\" readonly " . ($required ? "required" : "") . ">\r\n\t\t\t<input type=\"hidden\" name=\"" . $name . "[avatar]\" value=\"" . $value["avatar"] . "\">\r\n\t\t\t<input type=\"hidden\" name=\"" . $name . "[openid]\" value=\"" . $value["openid"] . "\">\r\n\t\t\t<input type=\"hidden\" name=\"" . $name . "[openid_wxapp]\" value=\"" . $value["openid_wxapp"] . "\">\r\n\t\t\t<span class=\"input-group-btn\">\r\n\t\t\t\t<button class=\"btn btn-default\" type=\"button\" onclick=\"showFansDialog(this);\">选择粉丝</button>\r\n\t\t\t</span>\r\n\t\t</div>\r\n\t\t<div class=\"input-group\" style=\"margin-top:.5em;\">\r\n\t\t\t<img src=\"" . $value["avatar"] . "\" onerror=\"this.src='" . $default . "'; this.title='头像未找到.'\" class=\"img-responsive img-thumbnail\" width=\"150\" />\r\n\t\t</div>";
    return $s;
}

function tpl_form_field_hello_wxapp_link($name, $value = "", $options = array())
{
    global $_GPC;
    $s = "";
    if (!defined("TPL_INIT_HELLO_WXAPP_LINK")) {
        $s = "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tfunction showHelloWxappLinkDialog(elm){\r\n\t\t\t\tirequire([\"web/hello\"],function(hello){\r\n\t\t\t\t\tvar ipt = \$(elm).parent().prev();\r\n\t\t\t\t\thello.selectWxappLink(function(href){\r\n\t\t\t\t\t\tipt.val(href);\r\n\t\t\t\t\t});\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t</script>";
        define("TPL_INIT_HElLO_WXAPP_LINK", true);
    }
    $s .= "\r\n\t<div class=\"input-group\">\r\n\t\t<input type=\"text\" value=\"" . $value . "\" name=\"" . $name . "\" class=\"form-control " . $options["css"]["input"] . "\" autocomplete=\"off\">\r\n\t\t<span class=\"input-group-btn\">\r\n\t\t\t<button class=\"btn btn-default " . $options["css"]["btn"] . "\" type=\"button\" onclick=\"showHelloWxappLinkDialog(this);\">选择链接</button>\r\n\t\t</span>\r\n\t</div>\r\n\t";
    return $s;
}
//获取wxapp链接
function wxapp_urls($type = 'mall')
{
    global $_W;
    global $_GPC;
    $data = array();
    if ($type == 'mall') {
        $data['platform']['sys'] = array('title' => '平台链接', 'items' => array(
            array('title' => '平台首页', 'url' => '/pages/home/home'),
            array('title' => '搜索商家','url' => '/pages/home/search'),
            array('title' => '会员中心','url' => '/pages/my/my'),
            array('title' => '我的订单','url' => '/pages/my/myorder/orderlist'),
            array('title' => '我的优惠券','url' => '/pages/my/mywallet/coupon'),
            array('title' => '我的收藏**','url' => ''),
        ));

        $data['platform']['dis'] = array('title' => '优惠活动', 'items' => array());

        $diypages = pdo_getall("hello_banbanjia_diypage",array("uniacid" => $_W['uniacid'],'version' => 2),array('id','name'));
        if(!empty($diypages)){
            $data['diyPages'] = $diypages;
        }

        $data['deliveryer']['sys'] = array('title' => '平台链接1', 'items' => array(array('title' => '平台首页', 'url' => 'pages/home/home')));
    }else{

    }
    return $data;
}
//隐藏表单
function tpl_form_filter_hidden($ctrls, $do = "web")
{
    global $_W;
    $html = "\r\n\t\t<input type=\"hidden\" name=\"c\" value=\"site\">\r\n\t\t<input type=\"hidden\" name=\"a\" value=\"entry\">\r\n\t\t<input type=\"hidden\" name=\"m\" value=\"hello_banbanjia\">\r\n\t\t<input type=\"hidden\" name=\"i\" value=\"" . $_W["uniacid"] . "\">\r\n\t\t<input type=\"hidden\" name=\"do\" value=\"" . $do . "\"/>\r\n\t";
    list($ctrl, $ac, $op, $ta) = explode("/", $ctrls);
    if (!empty($ctrl)) {
        $html .= "<input type=\"hidden\" name=\"ctrl\" value=\"" . $ctrl . "\"/>";
        if (!empty($ac)) {
            $html .= "<input type=\"hidden\" name=\"ac\" value=\"" . $ac . "\"/>";
        }
        if (!empty($ac)) {
            $html .= "<input type=\"hidden\" name=\"op\" value=\"" . $op . "\"/>";
            if (!empty($ta)) {
                $html .= "<input type=\"hidden\" name=\"ta\" value=\"" . $ta . "\"/>";
            }
        }
    }
    return $html;
}
//坐标
function tpl_form_field_hello_coordinate($field, $value = array(), $required = false)
{ 
    global $_W;
    $s = "";
    if (!defined("TPL_INIT_HELLO_COORDINATE")) {
        $s .= "<script type=\"text/javascript\">\r\n\t\t\t\tfunction showCoordinate(elm) {\r\n\t\t\t\t\tirequire([\"web/hello\"], function(hello){\r\n\t\t\t\t\t\tvar val = {};\r\n\t\t\t\t\t\tval.lng = parseFloat(\$(elm).parent().prev().prev().find(\":text\").val());\r\n\t\t\t\t\t\tval.lat = parseFloat(\$(elm).parent().prev().find(\":text\").val());\r\n\t\t\t\t\t\thello.map(val, function(r){\r\n\t\t\t\t\t\t\t\$(elm).parent().prev().prev().find(\":text\").val(r.lng);\r\n\t\t\t\t\t\t\t\$(elm).parent().prev().find(\":text\").val(r.lat);\r\n\t\t\t\t\t\t});\r\n\t\t\t\t\t});\r\n\t\t\t\t}\r\n\t\t\t</script>";
        define("TPL_INIT_HELLO_COORDINATE", true);
    }
    $s .= "\r\n\t\t<div class=\"row row-fix\">\r\n\t\t\t<div class=\"col-xs-4 col-sm-4\">\r\n\t\t\t\t<input type=\"text\" name=\"" . $field . "[lng]\" value=\"" . $value["lng"] . "\" placeholder=\"地理经度\"  class=\"form-control\" " . ($required ? "required" : "") . "/>\r\n\t\t\t</div>\r\n\t\t\t<div class=\"col-xs-4 col-sm-4\">\r\n\t\t\t\t<input type=\"text\" name=\"" . $field . "[lat]\" value=\"" . $value["lat"] . "\" placeholder=\"地理纬度\"  class=\"form-control\" " . ($required ? "required" : "") . "/>\r\n\t\t\t</div>\r\n\t\t\t<div class=\"col-xs-4 col-sm-4\">\r\n\t\t\t\t<button onclick=\"showCoordinate(this);\" class=\"btn btn-default\" type=\"button\">选择坐标</button>\r\n\t\t\t</div>\r\n\t\t</div>";
    return $s;
}
