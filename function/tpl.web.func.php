<?php

defined("IN_IA") or exit("Access Denied");

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
        $data['platform']['sys'] = array('title'=> '平台链接','items' => array(array('title'=>'平台首页','url'=>'pages/home/home')));
        $data['deliveryer']['sys'] = array('title'=> '平台链接1','items' => array(array('title'=>'平台首页','url'=>'pages/home/home')));
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