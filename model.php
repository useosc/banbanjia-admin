<?php

// use function Qiniu\json_decode;

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
    // ihttp_email();
}

mload()->lmodel('plugin');
function pload() //插件加载器
{
    static $ploader = NULL;
    if (empty($ploader)) {
        $ploader = new Ploader();
    }
    return $ploader;
}

function iurl($segment, $params = array(), $addhost = false) //生成链接
{
    global $_W;
    list($ctrl, $ac, $op, $ta) = explode("/", $segment);
    $params = array_merge(array("ctrl" => $ctrl, "ac" => $ac, "op" => $op, "ta" => $ta, "do" => "web", "m" => "hello_banbanjia"), $params);
    $url = iwurl("site/entry", $params);
    if (($_W['_ctrl'] == 'store' || $ctrl == 'store')) {
        $params['i'] = $_W['uniacid'];
        $url = iwurl('site/entry', $params, './business.php?');
    }
    if ($addhost) {
        $url = $_W["siteroot"] . "web/" . substr($url, 2);
    }
    return $url;
}

function imurl($segment, $params = array(), $addhost = false)
{ //手机端
    global $_W;
    list($ctrl, $ac, $op, $ta) = explode("/", $segment);
    $basic = array("ctrl" => $ctrl, "ac" => $ac, "op" => $op, "ta" => $ta, "do" => "mobile", "m" => "hello_banbanjia");
    $params = array_merge($basic, $params);
    $url = murl("entry", $params);
    if ($addhost) {
        $oauth_host = $_W["siteroot"];
        if (!empty($_W["we7_hello_banbanjia"]["config"]["oauth"]["oauth_host"])) {
            $oauth_host = $_W["we7_hello_banbanjia"]["config"]["oauth"]["oauth_host"];
        }
        $oauth_host = rtrim($oauth_host, "/");
        $url = $oauth_host . "/app/" . substr($url, 2);
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
//检查插件权限
function check_plugin_permit($name)
{
    global $_W;
    static $_plugins = array();
    if (isset($_plugins[$name])) {
        return $_plugins[$name];
    }
    $dir = WE7_BANBANJIA_PLUGIN_PATH . $name . "/inc";
    if (!is_dir($dir)) {
        $_plugins[$name] = false;
        return $_plugins[$name];
    }
    $plugin = pdo_get("hello_banbanjia_plugin", array("name" => $name), array("id", "name"));
    if (empty($plugin)) {
        $_plugins[$name] = false;
        return $_plugins[$name];
    }
    mload()->lmodel('common');
    $permits = get_account_permit();
    if (empty($permits) || in_array($name, $permits["plugins"])) {
        $_plugins[$name] = true;
    } else {
        $_plugins[$name] = false;
    }
    return $_plugins[$name];
}
//检查插件是否存在
function check_plugin_exist($name)
{
    global $_W;
    static $_plugins_exist = array();
    if (isset($_plugins_exist[$name])) {
        return $_plugins_exist[$name];
    }
    if (!empty($_W['_plugins'])) {
        $_plugins_exist[$name] = false;
        if (in_array($name, array_keys($_W['_plugins']))) {
            $_plugins_exist[$name] = true;
        }
    } else {
        $plugin = pdo_get('hello_banbanjia_plugin', array('name' => $name), array('id', 'name'));
        if (empty($plugin)) {
            $_plugins_exist[$name] = false;
            return $_plugins_exist[$name];
        }
        $_plugins_exist[$name] = true;
    }
    return $_plugins_exist[$name];
}

//获取轮播图
function sys_fetch_slide($type = 'homeTop', $format = false)
{
    global $_W;
    $slides = pdo_fetchall("select * from" . tablename("hello_banbanjia_slide") . "where uniacid = :uniacid  and type = :type and status = 1 order by displayorder desc", array(":uniacid" => $_W["uniacid"], ":type" => $type));
    if ($format) {
        foreach ($slides as &$slide) {
            $slide['thumb'] = tomedia($slide['thumb']);
        }
    }
    return $slides;
}
//系统日志
function slog($type, $title, $params, $message)
{
    global $_W;
    if (empty($type)) {
        return error(-1, "错误类型不能为空");
    }
    if (empty($message)) {
        return error(-1, "错误信息不能为空");
    }
    $data = array("uniacid" => $_W["uniacid"], "type" => $type, "title" => $title, "params" => iserializer($params), "message" => iserializer($message), "addtime" => TIMESTAMP);
    pdo_insert("hello_banbanjia_system_log", $data);
    return true;
}
//检验验证码
function icheck_verifycode($mobile, $code)
{
    global $_W;
    $isexist = pdo_fetch("select * from " . tablename("uni_verifycode") . " where uniacid = :uniacid and receiver = :receiver and verifycode = :verifycode and createtime >= :createtime", array(":uniacid" => $_W["uniacid"], ":receiver" => $mobile, ":verifycode" => $code, ":createtime" => time() - 1800));
    if (!empty($isexist)) {
        return true;
    }
    return false;
}
//格式化模板消息
function sys_wechat_tpl_format($params)
{
    $send = array();
    foreach ($params as $key => $param) {
        $send[$key] = array("value" => $param, "color" => "#ff510");
    }
    return $send;
}
//计算距离
//$distance_type = array("riding" => 2, "driving" => 1, "line" => 0, "walking" => 3);
function calculate_distance($origins, $destination, $type = 0)
{
    $query = array(
        'key' => AMAP_WEB_SERVICE_KEY,
        'destination' => implode(',', $destination),
    );
    if ($type == 2) {
        $query['origins'] = implode(',', $origins);
        $url = 'http://restapi.amap.com/v4/direction/bicycling?';
    } else {
        $query['origins'] = implode(',', $origins);
        $query['type'] = $type;
        $query['output'] = 'json';
        $url = 'https://restapi.amap.com/v3/distance?';
    }
    $query = http_build_query($query);
    load()->func('communication');
    $result = ihttp_get($url . $query);
    if (is_error($result)) {
        return $result;
    }
    $result = @json_decode($result['content'], true);
    if ($type == 2) {
        if (!empty($result['errcode'])) {
            if ($result['errcode'] == '30007') {
                $dis = calculate_distance($origins, $destination, 1);
                return $dis;
            }
            return error($result['errcode'], $result['errmsg']);
        }
        return round($result['data']['paths'][0]['distance'] / 1000, 3);
    } else {
        if ($result['status'] != 1) {
            return error(-1, $result['info']);
        }
        if (round($result['results'][0]['distance'] / 1000, 3) < 0 && $type == 3) {
            $dis = calculate_distance($origins, $destination, 2);
            return $dis;
        }
        return round($result['results'][0]['distance'] / 1000, 3);
    }
}
//保存字符串到文件
function ifile_put_contents($filename,$data)
{
    global $_W;
    load()->func('file');
    $filename = MODULE_ROOT . '/' . $filename;
    mkdirs(dirname($filename));
    file_put_contents($filename,$data);
    @chmod($filename,$_W['config']['setting']['filemode']);
    return is_file($filename);
}

function sys_notice_settle($sid,$type = "clerk",$note ="")
{//入驻通知
    global $_W;
    $store = store_fetch($sid,array('id','title','addtime','status','address'));
    if (empty($store)) {
        return error(-1, "公司不存在");
    }
    $store["manager"] = store_manager($sid);
    $store_status = array(1 => "审核通过", 2 => "审核中", 3 => "审核未通过");
    $acc = WeAccount::create($_W['acid']);
    if($type == 'clerk'){
        
    }
}
//企业标签
function category_store_label()
{
    global $_W;
    $data = pdo_fetchall("select id, title, alias,  color, is_system, displayorder from" . tablename("hello_banbanjia_category") . " where uniacid = :uniacid and type = :type order by is_system desc, displayorder desc", array(":uniacid" => $_W["uniacid"], ":type" => "QY_store_label"), "id");
    return $data;
}
/**
 * 计算两个坐标之间的距离(米)
 * @param float $fP1Lat 起点(纬度)
 * @param float $fP1Lon 起点(经度)
 * @param float $fP2Lat 终点(纬度)
 * @param float $fP2Lon 终点(经度)
 * @return int
 */
function distanceBetween($longitude1, $latitude1, $longitude2, $latitude2)
{
    $radLat1 = radian($latitude1);
    $radLat2 = radian($latitude2);
    $a = radian($latitude1) - radian($latitude2);
    $b = radian($longitude1) - radian($longitude2);
    $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
    $s = $s * 6378.137;
    $s = round($s * 10000) / 10000;
    return $s * 1000;
}
function radian($d)
{
    return $d * 3.1415926535898 / 180;
}
function array_sort($array, $sort_key, $sort_order = SORT_ASC)
{
    if (is_array($array)) {
        foreach ($array as $row_array) {
            $key_array[] = $row_array[$sort_key];
        }
        array_multisort($key_array, $sort_order, $array);
        return $array;
    } else {
        return false;
    }
}