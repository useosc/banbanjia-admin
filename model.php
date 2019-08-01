<?php
defined("IN_IA") or exit("Access Denied");
class Mloader
{
    private $cache = array();
    public function lfunc($name) //函数加载器

    {
        if (isset($this->cache["func"][$name])) {
            return true;
        }
        $file = WE7_BANBANJIA_PATH . "function/" . $name . ".func.php";
        if (file_exists($file)) {
            include $file;
            $this->cache["func"][$name] = true;
            return true;
        }
        trigger_error("Invalid Helper Function /addons/hello_banbanjia/function/" . $name . ".func.php", 256);
        return false;
    }
    public function lclass($name) //类加载器

    {
        if (isset($this->cache["class"][$name])) {
            return true;
        }
        $file = WE7_BANBANJIA_PATH . "class/" . $name . ".class.php";
        if (file_exists($file)) {
            include $file;
            $this->cache["class"][$name] = true;
            return true;
        }
        trigger_error("Invalid Class /addons/hello_banbanjia/class/" . $name . ".class.php", 256);
        return false;
    }
    public function lmodel($name) //数据模型加载

    {
        if (isset($this->cache["model"][$name])) {
            return true;
        }
        $file = WE7_BANBANJIA_PATH . "model/" . $name . ".mod.php";
        if (file_exists($file)) {
            include $file;
            $this->cache["model"][$name] = true;
            return true;
        }
        trigger_error("Invalid Model /addons/hello_banbanjia/model/" . $name . ".mod.php", 1024);
        return false;
    }
}

function mload() //返回加载器
{
    static $mloader = NULL;
    if (empty($mloader)) {
        $mloader = new Mloader();
    }
    return $mloader;
}

function iurl($segment, $params = array(), $addhost = false) //生成链接
{
    global $_W;
    list($ctrl, $ac, $op, $ta) = explode("/", $segment);
    $params = array_merge(array("ctrl" => $ctrl, "ac" => $ac, "op" => $op, "ta" => $ta, "do" => "web", "m" => "hello_banbanjia"), $params);
    $url = iwurl("site/entry", $params);
    if ($addhost) {
        $url = $_W["siteroot"] . "web/" . substr($url, 2);
    }
    return $url;
}

function iwurl($segment, $params = array(), $script = "./index.php?") //转换为完全链接
{
    list($controller, $action, $do) = explode("/", $segment);
    $url = $script;
    if (!empty($controller)) {
        $url .= "c=" . $controller . "&";
    }
    if (!empty($action)) {
        $url .= "a=" . $action . "&";
    }
    if (!empty($do)) {
        $url .= "do=" . $do . "&";
    }
    if (!empty($params)) {
        $queryString = http_build_query($params, "", "&");
        $url .= $queryString;
    }
    return $url;
}

function ifilter_url($params) //替换参数重新生成链接
{
    global $_W;
    if (empty($params)) {
        return "";
    }
    $query_arr = array();
    $parse = parse_url($_W["siteurl"]);
    if (!empty($parse["query"])) {
        $query = $parse["query"];
        parse_str($query, $query_arr);
    }
    $params = explode(",", $params);
    foreach ($params as $val) {
        if (!empty($val)) {
            $data = explode(":", $val);
            $query_arr[$data[0]] = trim($data[1]);
        }
    }
    $query_arr["page"] = 1;
    $query = http_build_query($query_arr);
    return "./index.php?" . $query;
}
//检查插件是否存在
function check_plugin_exist($name){
    global $_W;
    static $_plugins_exist = array();
    if(isset($_plugins_exist[$name])){
        return $_plugins_exist[$name];
    }
    if(!empty($_W['_plugins'])){
        $_plugins_exist[$name] = false;
        if(in_array($name,array_keys($_W['_plugins']))){
            $_plugins_exist[$name] = true;
        }
    }else{
        $plugin = pdo_get('hello_banbanjia_plugin',array('name' => $name),array('id','name'));
        if(empty($plugin)){
            $_plugins_exist[$name] = false;
            return $_plugins_exist[$name];
        }
        $_plugins_exist[$name] = true;
    }
    return $_plugins_exist[$name];
}