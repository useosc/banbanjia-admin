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
    $params = array_merge(array("ctrl" => $ctrl, "ac" => $ac, "op" => $op, "ta" => $ta, "do" => "web", "m" => "we7_hello_banbanjia"), $params);
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
