<?php
defined("IN_IA") or exit("Access Denied");
function itemplate($filename, $flag = TEMPLATE_DISPLAY)
{
    global $_W;
    global $_GPC;
    $module = "hello_banbanjia";
    if (defined("IN_SYS")) {
        if (!defined('IN_PLUGIN')) {
            $source = WE7_BANBANJIA_PATH . "template/web/" . $filename . ".html";
        } else { //编译插件
            $filename_old = $filename;
            $filename = (string) $_W['_plugin']['name'] . '/template/web/' . $filename . '.html';
            $source = WE7_BANBANJIA_PLUGIN_PATH . $filename;
            if(defined("IN_AGENT_PLUGIN")){

            }else{
                if(defined("IN_GOHOME_WPLUGIN")){
                    $source = WE7_BANBANJIA_PLUGIN_PATH . "gohome/" . $_W['_ctrl'] . "/template/web/" . $filename_old . ".html";
                    if (!is_file($source) || $filename_old == "tabs") {
                        $source = WE7_BANBANJIA_PLUGIN_PATH . "gohome/template/web/" . $filename_old . ".html";
                    }
                }
            }
            if (!is_file($source)) {
                $source = WE7_BANBANJIA_PATH . 'template/web/' . $filename_old . '.html';
            }
        }
        $compile = IA_ROOT . "/data/tpl/web/" . $_W["template"] . "/" . $module . "/" . $filename . ".tpl.php";
    }else{ //公众号

    }

    //开始编译
    if (!is_file($source)) {
        exit("Error: template file '" . $filename . "' is not exist!");
    }
    $paths = pathinfo($compile); //编译文件路径信息
    $compile = str_replace($paths["filename"], $_W["uniacid"] . "_" . $paths["filename"], $compile);
    if (DEVELOPMENT || !is_file($compile) || filemtime($compile) < filemtime($source)) {
        itemplate_compile($source, $compile, false);
    }
    return $compile;
}
function itemplate_compile($from, $to, $inmodule = false) //模板编译函数

{
    $path = dirname($to);
    if (!is_dir($path)) {
        load()->func("file");
        mkdirs($path);
    }
    $content = itemplate_parse(file_get_contents($from), $inmodule);
    if (IMS_FAMILY == "x" && !preg_match("/(footer|header|account\/welcome|login|register)+/", $from)) {
        $content = str_replace("微擎", "系统", $content);
    }
    file_put_contents($to, $content);
}
function itemplate_parse($str, $inmodule = false) //字符串解析函数

{
    global $_W;
    global $_GPC;
    $str = preg_replace("/<!--{(.+?)}-->/s", "{\$1}", $str);
    $str = preg_replace("/{template\\s+(.+?)}/", "<?php (!empty(\$this) && \$this instanceof WeModuleSite || " . intval($inmodule) . ") ? (include \$this->template(\$1, TEMPLATE_INCLUDEPATH)) : (include template(\$1, TEMPLATE_INCLUDEPATH));?>", $str);
    $str = preg_replace("/{itemplate\\s+(.+?)}/", "<?php include itemplate(\$1, TEMPLATE_INCLUDEPATH);?>", $str);
    $str = preg_replace("/{php\\s+(.+?)}/", "<?php \$1?>", $str);
    $str = preg_replace("/{if\\s+(.+?)}/", "<?php if(\$1) { ?>", $str);
    $str = preg_replace("/{else}/", "<?php } else { ?>", $str);
    $str = preg_replace("/{else ?if\\s+(.+?)}/", "<?php } else if(\$1) { ?>", $str);
    $str = preg_replace("/{\\/if}/", "<?php } ?>", $str);
    $str = preg_replace("/{ifp\\s+(.+?)\\s+\\|\\|\\s+(.+?)\\s+\\|\\|\\s+(.+?)\\s+\\|\\|\\s+(.+?)}/", "<?php if(check_permit(\$1) || check_permit(\$2) || check_permit(\$3) || check_permit(\$4)) { ?>", $str);
    $str = preg_replace("/{ifp\\s+(.+?)\\s+\\|\\|\\s+(.+?)\\s+\\|\\|\\s+(.+?)}/", "<?php if(check_permit(\$1) || check_permit(\$2) || check_permit(\$3)) { ?>", $str);
    $str = preg_replace("/{ifp\\s+(.+?)\\s+\\|\\|\\s+(.+?)}/", "<?php if(check_permit(\$1) || check_permit(\$2)) { ?>", $str);
    $str = preg_replace("/{ifp\\s+(.+?)}/", "<?php if(check_permit(\$1)) { ?>", $str);
    $str = preg_replace("/{loop\\s+(\\S+)\\s+(\\S+)}/", "<?php if(is_array(\$1)) { foreach(\$1 as \$2) { ?>", $str);
    $str = preg_replace("/{loop\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)}/", "<?php if(is_array(\$1)) { foreach(\$1 as \$2 => \$3) { ?>", $str);
    $str = preg_replace("/{\\/loop}/", "<?php } } ?>", $str);
    $str = preg_replace("/{(\\\$[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)}/", "<?php echo \$1;?>", $str);
    $str = preg_replace("/{(\\\$[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\[\\]'\\\"\\\$]*)}/", "<?php echo \$1;?>", $str);
    $str = preg_replace("/{url\\s+(\\S+)}/", "<?php echo url(\$1);?>", $str);
    $str = preg_replace("/{url\\s+(\\S+)\\s+(array\\(.+?\\))}/", "<?php echo url(\$1, \$2);?>", $str);
    $str = preg_replace("/{media\\s+(\\S+)}/", "<?php echo tomedia(\$1);?>", $str);
    $str = preg_replace_callback("/<\\?php([^\\?]+)\\?>/s", "template_addquote", $str);
    $str = preg_replace("/{([A-Z_\\x7f-\\xff][A-Z0-9_\\x7f-\\xff]*)}/s", "<?php echo \$1;?>", $str);
    $str = str_replace("{##", "{", $str);
    $str = str_replace("##}", "}", $str);
    if (!empty($GLOBALS["_W"]["setting"]["remote"]["type"])) {
        $str = str_replace("</body>", "<script>\$(function(){\$('img').attr('onerror', '').on('error', function(){if (!\$(this).data('check-src') && (this.src.indexOf('http://') > -1 || this.src.indexOf('https://') > -1)) {this.src = this.src.indexOf('" . $GLOBALS["_W"]["attachurl"] . "') == -1 ? this.src.replace('" . $GLOBALS["_W"]["attachurl_remote"] . "', '" . $GLOBALS["_W"]["attachurl"] . "') : this.src.replace('" . $GLOBALS["_W"]["attachurl"] . "', '" . $GLOBALS["_W"]["attachurl_remote"] . "');\$(this).data('check-src', true);}});});</script></body>", $str);
    }
    $str = "<?php defined('IN_IA') or exit('Access Denied');?>" . $str;
    return $str;
}

function iaes_pkcs7_decode($encrypt_data, $key, $iv = false) //微信小程序解密函数
{
    // mload()->lclass("pkcs7");
    mload()->lclass('dataCrypt');
    // $encrypt_data = base64_decode($encrypt_data);
    // if (!empty($iv)) {
    //     $iv = base64_decode($iv);
    // }
    // $pc = new Prpcrypt($key);
    $pc = new WXBizDataCrypt($key);
    $result = $pc->decryptData($encrypt_data, $iv);
    // $result = $pc->decrypt($encrypt_data, $iv);
    if ($result[0] != 0) {
        return error($result[0], "解密失败");
    }
    return $result[1];
}
//手机号是否合法
function is_validMobile($mobile)
{
    if (preg_match("/^[01][3456789][0-9]{9}\$/", $mobile) || preg_match("/^[8][0-9]{11}\$/", $mobile)) {
        return true;
    }
    return false;
}
// 是否是小程序端
function is_wxapp()
{
    global $_W;
    if (defined('IN_WXAPP') && $_GPC['from'] == 'wxapp') {
        return true;
    }
    return false;
}

//生成32位uuid
if(!function_exists('uuid'))
{
   function uuid($prefix = '')   
  {   
    $chars = md5(uniqid(mt_rand(), true));   
    $uuid  = substr($chars,0,8) . '-';   
    $uuid .= substr($chars,8,4) . '-';   
    $uuid .= substr($chars,12,4) . '-';   
    $uuid .= substr($chars,16,4) . '-';   
    $uuid .= substr($chars,20,12);   
    return $prefix . $uuid;   
  }  
}

//请求bpm系统
// if(!)