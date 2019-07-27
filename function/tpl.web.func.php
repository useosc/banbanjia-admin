<?php

defined("IN_IA") or exit("Access Denied");

function tpl_form_field_hello_wxapp_link($name, $value = "", $options = array())
{ 
    global $_GPC;
    $s = "";
    if(!defined("TPL_INIT_HELLO_WXAPP_LINK")){
        $s = "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tfunction showHelloWxappLinkDialog(elm){\r\n\t\t\t\tirequire([\"web/hello\"],function(hello){\r\n\t\t\t\t\tvar ipt = \$(elm).parent().prev();\r\n\t\t\t\t\thello.selectWxappLink(function(href){\r\n\t\t\t\t\t\tipt.val(href);\r\n\t\t\t\t\t});\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t</script>";
        define("TPL_INIT_HElLO_WXAPP_LINK",true);
    }
    $s .= "\r\n\t<div class=\"input-group\">\r\n\t\t<input type=\"text\" value=\"" . $value . "\" name=\"" . $name . "\" class=\"form-control " . $options["css"]["input"] . "\" autocomplete=\"off\">\r\n\t\t<span class=\"input-group-btn\">\r\n\t\t\t<button class=\"btn btn-default " . $options["css"]["btn"] . "\" type=\"button\" onclick=\"showHelloWxappLinkDialog(this);\">选择链接</button>\r\n\t\t</span>\r\n\t</div>\r\n\t";
    return $s;
}
