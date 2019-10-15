<?php
defined("IN_IA") or exit("Access Denied");

if (!function_exists("get_system_config")) { //获取系统配置
    function get_system_config($key = "", $uniacid = -1)
    {
        global $_W;
        if ($uniacid == -1) {
            $uniacid = intval($_W["uniacid"]);
        }
        $config = pdo_get("hello_banbanjia_config", array("uniacid" => $uniacid), array("sysset", "pluginset", "id"));
        if (empty($config["id"])) {
            $init_config = array("uniacid" => $uniacid);
            pdo_insert("hello_banbanjia_config", $init_config);
            return array();
        }
        $sysset = iunserializer($config["sysset"]); //反序列化系统设置
        if (!is_array($sysset)) {
            $sysset = array();
        }
        $pluginset = iunserializer($config["pluginset"]);
        if (!is_array($pluginset)) {
            $pluginset = array();
        }
        if (!empty($sysset["platform"]["logo"])) { //平台logo
            $sysset["platform"]["logo"] = tomedia($sysset["platform"]["logo"]);
        }
        if (empty($key)) {
            return $sysset;
        }
        $keys = explode(".", $key);
        $counts = count($keys);
        if ($counts == 1) {
            return $sysset[$key];
        }
        if ($counts == 2) {
            return $sysset[$keys[0]][$keys[1]];
        }
        if ($counts == 3) {
            return $sysset[$keys[0]][$keys[1]][$keys[2]];
        }
    }
}

//系统设置
function set_system_config($key, $value)
{
    global $_W;
    $sysset = get_system_config();
    $keys = explode('.', $key);
    $counts = count($keys);
    if ($counts == 1) {
        $sysset[$keys[0]] = $value;
    } else {
        if ($counts == 2) {
            if (!is_array($sysset[$keys[0]])) {
                $sysset[$keys[0]] = array();
            }
            $sysset[$keys[0]][$keys[1]] = $value;
        } else {
            if ($counts == 3) {
                if (!is_array($sysset[$keys[0]])) {
                    $sysset[$keys[0]] = array();
                } else {
                    if (!is_array($sysset[$keys[0]][$keys[1]])) {
                        $sysset[$keys[0]][$keys[1]] = array();
                    }
                }
                $sysset[$keys[0]][$keys[1]][$keys[2]] = $value;
            }
        }
    }
    pdo_update('hello_banbanjia_config', array('sysset' => iserializer($sysset)), array('uniacid' => $_W['uniacid']));
    return true;
}

function get_global_config($key = "") //获取全局配置

{
    $result = get_system_config($key, 0);
    return $result;
}

//设置文章
function set_config_text($title, $name, $value = "")
{
    global $_W;
    $config = pdo_get("hello_banbanjia_text", array("uniacid" => $_W["uniacid"], "name" => $name));
    if (empty($config)) {
        $data = array("uniacid" => $_W["uniacid"], "name" => $name, "title" => $title, "value" => is_array($value) ? iserializer($value) : $value);
        pdo_insert("hello_banbanjia_text", $data);
    } else {
        $data = array("uniacid" => $_W["uniacid"], "title" => $title, "value" => is_array($value) ? iserializer($value) : $value);
        pdo_update("hello_banbanjia_text", $data, array("uniacid" => $_W["uniacid"], "name" => $name));
    }
    return true;
}
function get_config_text($name)
{
    global $_W;
    $config = pdo_get("hello_banbanjia_text", array("uniacid" => $_W["uniacid"], "name" => $name));
    if ($name = "carry_delivery_time") {
        $config["value"] = iunserializer($config["value"]);
    }
    return $config["value"];
}
//检查权限
function check_permit($permit, $redirct = false)
{
    global $_W;
    // $redircts = array("common", "store");
    // if (in_array($_W["_ctrl"], $redircts)) {
    //     return true;
    // }
    $redircts_ac = array('oauth');
    if(in_array($_W['_ac'],$redircts_ac)){
        return true;
    }
    if ($_W["isfounder"] == 1 || $_W["permits"] == "all") {
        return true;
    }
    if (empty($_W["permits"])) {
        return false;
    }
    if ($permit == "plugin.index") {
        return true;
    }
    if (in_array($permit, $_W["permits"])) {
        return true;
    }
    if (defined("IN_PLATEFORM")) {
        $all_permits = array();
        if ($_W["plateformer"]["usertype"] == "agenter") {
            $all_permits = get_agent_permits();
        } else {
            $all_permits = get_all_permits(true);
        }
        if (!in_array($permit, $all_permits)) {
            return true;
        }
    }
    if ($redirct) {
        $permits_init = array("dashboard.index", "merchant.store", "order.takeout", "statcenter.takeout", "paycenter.paybill", "merchant.store", "service.comment", "deliveryer.account", "clerk.account", "member.index", "config.mall", "errander.index", "bargain.index", "deliveryCard.index", "qianfanApp.index", "majiaApp.index", "shareRedpacket.index", "freeLunch.index", "diypage.index", "ordergrant.index", "superRedpacket.index", "creditshop.index", "agent.index", "wheel.index", "gohome.index", "svip.index", "spread.index", "advertise.index", "cloudGoods.index", "mealRedpacket.index", "storebd.index", "zhunshibao.index");
        if (in_array($permit, $permits_init)) {
            $permit_arr = explode(".", $permit);
            foreach ($_W["permits"] as $row) {
                if (strexists($row, (string) $permit_arr[0] . ".")) {
                    $permit = explode(".", $row);
                    header("location:" . iurl((string) $permit["0"] . "/" . $permit["1"]));
                    exit;
                }
            }
            return false;
        }
    }
    return false;
}
//插件配置
if (!function_exists('get_plugin_config')) {
    function get_plugin_config($key = '')
    {
        global $_W;
        $_W['uniacid'] = intval($_W['uniacid']);
        $config = pdo_get('hello_banbanjia_config', array('uniacid' => $_W['uniacid']), array('pluginset'));
        if (empty($config)) {
            return array();
        }
        $pluginset = iunserializer($config["pluginset"]);
        if (empty($key)) {
            return $pluginset;
        }
        $keys = explode('.', $key);
        $plugin = $keys[0];
        if (!empty($plugin)) {
            $config_plugin = $pluginset[$plugin];
            if (!is_array($config_plugin)) {
                return array();
            }
            $count = count($keys);
            if ($count == 2) {
                return $config_plugin[$keys[1]];
            }
            if ($count == 3) {
                return $config_plugin[$keys[1]][$keys[2]];
            }
            return $config_plugin;
        }
    }
}
//检查公众号对插件的权限
function get_account_permit($key = '', $uniacid = 0)
{
    global $_W;
    if (empty($uniacid)) {
        $uniacid = $_W['uniacid'];
    }
    $permit = pdo_get('hello_banbanjia_permit_account', array('uniacid' => $uniacid));
    if (empty($permit)) {
        return false;
    }
    if (!empty($permit)) {
        $permit["plugins"] = iunserializer($permit["plugins"]);
        if (!is_array($permit["plugins"])) {
            $permit["plugins"] = array();
        }
        if (empty($permit["plugins"])) {
            $permit["plugins"] = array("none");
        }
        if (!empty($key)) {
            return $permit[$key];
        }
    }
    return $permit;
}
//设置插件配置
function set_plugin_config($key, $value)
{
    global $_W;
    $keys = explode(".", $key);
    $counts = count($keys);
    $pluginset = get_plugin_config();
    $config_plugin = $pluginset[$keys[0]];
    if ($counts == 1) {
        $config_plugin = $value;
    } else {
        if ($counts == 2) {
            $config_plugin[$keys[1]] = $value;
        } else {
            if ($counts == 3) {
                $config_plugin[$keys[1]][$keys[2]] = $value;
            }
        }
    }
    $pluginset[$keys[0]] = $config_plugin;
    pdo_update("hello_banbanjia_config", array("pluginset" => iserializer($pluginset)), array("uniacid" => $_W["uniacid"]));
    return true;
}


//日志
function mlog($type, $log_id = 0, $message = '')
{
    global $_W;
    if (empty($type)) {
        return error(-1, '日志类型不能为空');
    }
    $type_info = mlog_types($_W["role"], $type);
    if (empty($type_info["type"])) {
        return error(-1, "日志类型有误");
    }
    $content = sprintf($type_info["content"], $log_id, $message);
    $data = array('uniacid' => $_W['uniacid'], 'username' => $_W['role_cn'], 'uid' => $_W['uid'], 'role' => $_W['role'], 'type' => $type, 'content' => $content, 'ip' => CLIENT_IP, 'address' => '', 'source' => '', 'addtime' => TIMESTAMP);
    pdo_insert('hello_banbanjia_operate_log', $data);
    return true;
}
//日志类型
function mlog_types($role = '', $value = 0)
{
    if ($role == 'operator') {
        $role = 'manager';
    }
    $common = array('1000' => array('key' => 1000, 'type' => '订单完成', 'content' => "订单完成(订单id:%s)"), '1002' => array('key' => 1002, 'type' => '订单取消', 'content' => "订单取消(订单id:%s)"), '3000' => array('key' => 3000, 'type' => '添加员工', 'content' => "添加员工(员工id:%s),详情：%s"), '3002' => array('key' => 3002, 'type' => '删除员工', 'content' => "删除员工(员工id:%s),详情:%s"), '4000' => array('key' => 4000, 'type' => '添加搬运工', 'content' => "添加搬运工(搬运工id:%s),详情：%s"), '4002' => array('key' => 4002, 'type' => '删除搬运工', 'content' => "删除搬运工(搬运工id:%s),详情：%s"));
    $type_all = array("manager" => array("1001" => array("key" => 1001, "type" => "订单部分退款", "content" => "平台发起订单部分退款(退款id:%s)"), "1004" => array("key" => 1004, "type" => "订单已退款", "content" => "平台将订单设为已退款(订单id:%s)"), "1005" => array("key" => 1005, "type" => "订单发起退款", "content" => "平台发起订单退款(订单id:%s)"), "2000" => array("key" => 2000, "type" => "添加商户", "content" => "平台后台添加商户(商户id:%s)"), "2001" => array("key" => 2001, "type" => "删除商户", "content" => "平台后台删除商户(商户id:%s)"), "2002" => array("key" => 2002, "type" => "商户加入回收站", "content" => "平台后台将商户加入回收站(商户id:%s)"), "2003" => array("key" => 2003, "type" => "商户入驻审核通过", "content" => "商户入驻审核通过(商户id:%s), 备注：%s"), "2004" => array("key" => 2004, "type" => "商户入驻审核不通过", "content" => "商户入驻审核不通过(商户id:%s), 备注：%s"), "2005" => array("key" => 2005, "type" => "平台变动商户账户", "content" => "后台变动商户账户(记录id:%s), 备注：%s"), "2006" => array("key" => 2006, "type" => "撤销商户提现", "content" => "撤销商户提现(记录id:%s), 备注：%s"), "2007" => array("key" => 2007, "type" => "商户提现打款", "content" => "商户提现打款(记录id:%s), 结果:%s"), "2008" => array("key" => 2008, "type" => "商户提现设为已处理", "content" => "商户提现设为已处理(记录id:%s)"), "2010" => array("key" => 2010, "type" => "平台创建商户活动", "content" => "平台创建商户活动，详情：%s"), "2011" => array("key" => 2011, "type" => "更改商户提现账户", "content" => "平台更改商户提现账户(商户id:%s)"), "2012" => array("key" => 2012, "type" => "更改商户营业状态", "content" => "平台更改商户营业状态(商户id:%s)"), "3001" => array("key" => 3001, "type" => "编辑店员", "content" => "平台编辑店员(店员id:%s)"), "4001" => array("key" => 4001, "type" => "编辑配送员", "content" => "平台编辑配送员(配送员id:%s)"), "4003" => array("key" => 4003, "type" => "后台变动配送员账户", "content" => "后台变动配送员账户(记录id:%s)"), "4004" => array("key" => 4004, "type" => "撤销配送员提现", "content" => "撤销配送员提现(记录id:%s), 备注：%s"), "4005" => array("key" => 4005, "type" => "配送员提现打款", "content" => "配送员提现打款(记录id:%s), 结果:%s"), "4006" => array("key" => 4006, "type" => "配送员提现设为已处理", "content" => "配送员提现设为已处理(记录id:%s)"), "4007" => array("key" => 4007, "type" => "配送员加入回收站", "content" => "平台将配送员加入回收站(配送员id:%s)"), "4008" => array("key" => 4008, "type" => "将配送员从回收站中恢复", "content" => "平台将配送员从回收站中恢复(配送员id:%s)"), "5000" => array("key" => 5000, "type" => "添加代理", "content" => "平台后台添加代理(代理id:%s)"), "5001" => array("key" => 5001, "type" => "删除代理", "content" => "平台后台删除代理(代理id:%s)"), "5002" => array("key" => 5002, "type" => "平台变动代理账户", "content" => "平台变动代理账户(记录id:%s), 备注：%s"), "5003" => array("key" => 5003, "type" => "撤销代理提现", "content" => "撤销代理提现(记录id:%s, 备注：%s)"), "5004" => array("key" => 5004, "type" => "代理提现打款", "content" => "代理提现打款(记录id:%s), 结果:%s"), "5005" => array("key" => 5005, "type" => "代理提现设为已处理", "content" => "代理提现设为已处理(记录id:%s)"), "5007" => array("key" => 5007, "type" => "更改代理提现账户", "content" => "平台更改代理提现账户(代理id:%s)"), "6000" => array("key" => 6000, "type" => "顾客加入黑名单", "content" => "顾客加入黑名单(顾客id:%s)"), "6001" => array("key" => 6001, "type" => "删除顾客", "content" => "平台后台删除顾客(uid:%s)"), "6002" => array("key" => 6002, "type" => "平台变动顾客账户", "content" => "平台变动顾客账户(uid:%s), 详情:%s"), "6003" => array("key" => 6003, "type" => "平台编辑顾客", "content" => "平台编辑顾客(uid:%s)"), "6004" => array("key" => 6004, "type" => "平台设置顾客等级", "content" => "平台设置顾客等级(uid:%s), 设置等级id:%s"), "6005" => array("key" => 6005, "type" => "平台设置顾客配送会员卡", "content" => "平台设置顾客配送会员卡(uid:%s), 详情:%s"), "6006" => array("key" => 6006, "type" => "平台设置顾客超级会员", "content" => "平台设置顾客超级会员(uid:%s), 详情:%s"), "6007" => array("key" => 6007, "type" => "顾客移出黑名单", "content" => "顾客移出黑名单(顾客id:%s)")), "agenter" => array("1001" => array("key" => 1001, "type" => "订单部分退款", "content" => "代理发起订单部分退款(退款id:%s)"), "1004" => array("key" => 1004, "type" => "订单已退款", "content" => "代理将订单设为已退款(订单id:%s)"), "1005" => array("key" => 1005, "type" => "订单发起退款", "content" => "代理发起订单退款(订单id:%s)"), "2000" => array("key" => 2000, "type" => "添加商户", "content" => "代理后台添加商户(商户id:%s)"), "2001" => array("key" => 2001, "type" => "删除商户", "content" => "代理后台删除商户(商户id:%s)"), "2002" => array("key" => 2002, "type" => "商户加入回收站", "content" => "代理后台将商户加入回收站(商户id:%s)"), "2003" => array(
        "key" => 2003, "type" => "商户入驻审核通过", "content" => "代理商户入驻审核通过(商户id:%s), 备注：%s"
    ), "2004" => array("key" => 2004, "type" => "商户入驻审核不通过", "content" => "代理商户入驻审核不通过(商户id:%s), 备注：%s"), "2011" => array("key" => 2011, "type" => "更改商户提现账户", "content" => "代理更改商户提现账户(商户id:%s)"), "2012" => array("key" => 2012, "type" => "更改商户营业状态", "content" => "代理更改商户营业状态(商户id:%s)"), "3001" => array("key" => 3001, "type" => "编辑店员", "content" => "代理编辑店员(店员id:%s)"), "4001" => array("key" => 4001, "type" => "编辑配送员", "content" => "代理编辑配送员(配送员id:%s)"), "4007" => array("key" => 4007, "type" => "配送员加入回收站", "content" => "代理将配送员加入回收站(配送员id:%s)"), "4008" => array("key" => 4008, "type" => "将配送员从回收站中恢复", "content" => "代理将配送员从回收站中恢复(配送员id:%s)"), "5006" => array("key" => 5006, "type" => "代理发起提现申请", "content" => "代理发起提现申请(记录id:%s)"), "5007" => array("key" => 5007, "type" => "更改代理提现账户", "content" => "代理更改提现账户(代理id:%s)")), "clerker" => array("1001" => array("key" => 1001, "type" => "订单部分退款", "content" => "商户发起订单部分退款(退款id:%s)"), "2009" => array("key" => 2009, "type" => "商户发起提现申请", "content" => "商户发起提现申请(记录id:%s)"), "2011" => array("key" => 2011, "type" => "更改商户提现账户", "content" => "商户更改提现账户(商户id:%s)"), "2012" => array("key" => 2012, "type" => "更改商户营业状态", "content" => "商户更改营业状态(商户id:%s)")));
    if (!empty($value)) {
        $type = $common[$value];
        if (empty($type)) {
            if ($role == 'founder') {
                $type = $type_all['manager'][$value];
            } else {
                $type = $type_all[$role][$value];
            }
        }
    }
    if (empty($role) || $role == "founder") {
        $types = array_merge($common, $type_all["manager"], $type_all["agenter"], $type_all["clerker"]);
    } else {
        $types = array_merge($common, $type_all[$role]);
    }
    if (empty($value)) {
        return $types;
    }
    return $type;
}

//获取所有日志信息
function mlog_fetch_all($filter = array())
{
    global $_W;
    global $_GPC;
    if (empty($filter)) {
        $filter = $_GPC;
    }
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $role = trim($filter["role"]);
    if (!empty($role)) {
        $condition .= " and role = :role";
        $params[":role"] = $role;
    }
    $type = intval($filter["type"]);
    if (!empty($type)) {
        $condition .= " and type = :type";
        $params[":type"] = $type;
    }
    $keyword = trim($filter["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and username like :keyword";
        $params[":keyword"] = "%" . $keyword . "%";
    }
    $page = max(1, intval($filter["page"]));
    $psize = intval($filter["psize"]) ? intval($filter["psize"]) : 20;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_operate_log") . $condition, $params);
    $logs = pdo_fetchall("select * from " . tablename("hello_banbanjia_operate_log") . $condition . " order by id desc limit " . ($page - 1) * $psize . "," . $psize, $params);
    if (!empty($logs)) {
        foreach ($logs as &$val) {
            $log_type = mlog_types($val["role"], $val["type"]);
            $val["type_cn"] = $log_type["type"];
            $val["addtime_cn"] = date("Y-m-d H:i", $val["addtime"]);
        }
    }
    $pager = pagination($total, $page, $psize);
    return array("logs" => $logs, "pager" => $pager);
}

//获取可用的支付方式
function get_available_payment($order_type = "", $sid = 0, $all = false, $orderType = 1)
{
    global $_W;
    $payment = $_W["we7_hello_banbanjia"]["config"]["payment"];
    if (empty($order_type)) {
        $payment = $payment["weixin"];
    } else {
        if (is_wxapp()) {
            $payment = $payment['wxapp'];
        }
    }

    if ($all) {
        $routers = array(
            "alipay" => array("title" => "支付宝", "value" => "alipay"),
            "wechat" => array("title" => "微信支付", "value" => "wechat"),
            "credit" => array("title" => "余额支付", "value" => "credit"),
            "delivery" => array("title" => "货到付款", "value" => "delivery"),
            "yimafu" => array("title" => "一码付", "value" => "yimafu"),
            "peerpay" => array("title" => "找朋友代付", "value" => "peerpay")
        );
        $payments = array();
        foreach ($payment as $item) {
            $payments[] = $routers[$item];
        }

        $test = array(
            array("title" => "微信支付", "value" => "wechat"),
            array("title" => "支付宝", "value" => "alipay"),
            array("title" => "余额支付", "value" => "credit")
        );

        // return $payments;
        return $test;
    } else {
        return $payment;
    }
}
//获取可用插件
function get_available_plugin()
{
    global $_W;
    mload()->lmodel('plugin');
    $plugins = plugin_fetchall();
    $array = array();
    $plugin_config = get_plugin_config();
    foreach ($plugins as $row) {
        $array[] = $row["name"];
    }
    return $array;
}

//获取用户信息
function get_user($uid = 0)
{
    global $_W;
    if (empty($uid)) {
        $uid = $_W['uid'];
    }
    $user = pdo_fetch("select a.*,b.permits as permits_role from " . tablename("hello_banbanjia_permit_user") . " as a left join " . tablename("hello_banbanjia_permit_role") . " as b on a.roleid = b.id where a.uniacid = :uniacid and a.uid = :uid", array(":uniacid" => $_W['uniacid'], ":uid" => $uid));
    if (empty($user)) {
        return false;
    }
    $user['permits_role'] = explode(',', $user['permits_role']);
    $user['permits'] = explode(',', $user['permits']);
    $user["permits"] = array_merge($user["permits"], $user["permits_role"]);
    return $user;
}

//获取公司员工
function get_clerk($uid = 0)
{
    global $_W;
    if (empty($uid)) {
        $uid = $_W['uid'];
    }
    $user = pdo_fetch("select a.*,b.permits as permits_role from " . tablename("hello_banbanjia_store_clerk") . " as a left join " . tablename("hello_banbanjia_clerk_role") . " as b on a.roleid = b.id where a.uniacid = :uniacid and a.clerk_id = :uid", array(":uniacid" => $_W['uniacid'], ":uid" => $uid));
    if (empty($user)) {
        return false;
    }
    if ($user['roleid'] == 0) { //公司管理员
        $user['permits'] = 'all';
    } else {
        $user['permits_role'] = explode(',', $user['permits_role']);
        $user['permits'] = explode(',', $user['permits']);
        $user["permits"] = array_merge($user["permits"], $user["permits_role"]);
    }
    return $user;
}

// 检查缓存是否超时
function check_cache_status($key, $timelimit = 300)
{
    global $_W;
    $cache = cache_read($key);
    if (empty($cache) || 0 < $cache["starttime"] && $cache["starttime"] + $timelimit < TIMESTAMP) {
        return false;
    }
    return true;
}

// 获取所有权限列表（商户）
function get_all_store_permits($justkey = false)
{
    $all_permits = array(
        'dashboard' => array(
            'title' => '概况',
            'permits' => array(
                'dashboard.index' => '运营概况'
            )
        ),
        'member' => array(
            'title' => '客户',
            'permits' => array(
                'member.index' => '概况',
                'member.list' => '列表',
                'member.consult' => '咨询'
            )
        ),
    );
    if ($justkey) {
        $permits = array();
        foreach ($all_permits as $key => $item) {
            $permits[] = $key;
            if (!empty($item['permits'])) {
                foreach ($item['permits'] as $key1 => $item1) {
                    $permits[] = $key1;
                }
            }
        }
        return $permits;
    } else {
        return $all_permits;
    }
}

//获取所有权限列表（平台）
function get_all_permits($justkey = false)
{
    $all_permits = array(
        'dashboard' => array(
            'title' => '概况',
            'permits' => array(
                'dashboard.index' => '运营概况',
                'dashboard.ad' => '引导页',
                'dashboard.slide' => '幻灯片'
            )
        ),
        'order' => array(
            'title' => '订单',
            'permits' => array(
                'order.new' => '未完成',
                'order.dispatch' => '调度中心-待指派',
                'order.records' => '调度中心-接单统计/接单记录'
            )
        ),
        'statcenter' => array( //
            'title' => '数据',
            'permits' => array()
        ),
        'service' => array(
            'title' => '客服',
            'permits' => array(
                'service.index' => '数据概况',
                'service.from' => '客服工作台（非客服不需要)',
                'service.user' => '客服管理',
                'service.group' => '分组管理',
                'service.words' => '常用语管理',
                'service.system' => '设置',
                'service.chatlog' => '历史消息'
            )
        )
    );
    if ($justkey) {
        $permits = array();
        foreach ($all_permits as $key => $item) {
            $permits[] = $key;
            if (!empty($item['permits'])) {
                foreach ($item['permits'] as $key1 => $item1) {
                    $permits[] = $key1;
                }
            }
        }
        return $permits;
    } else {
        return $all_permits;
    }
}
