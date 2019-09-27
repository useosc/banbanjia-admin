<?php

/**
 * hello_banbanjia模块微站定义
 *
 * @author hellorobot
 * @url
 */
defined('IN_IA') or exit('Access Denied');
include "version.php";
include "defines.php";
include "model.php"; //加载器（链接）相关

class Hello_banbanjiaModuleSite extends WeModuleSite
{

    public function __construct()
    { }
    public function doWebTest() //测试

    { }
    public function doWebWeb() //后台管理

    {
        ini_set("display_errors", "1"); //显示出错信息
        // error_reporting(E_ALL ^ E_NOTICE);
        // error_reporting(E_ALL);
        $this->router(); //路由函数
    }

    public function doMobileMobile() //接口
    {
        ini_set("display_errors", "1"); //显示出错信息
        $this->router();
    }

    public function router() //路由函数

    {
        $bootstrap = WE7_BANBANJIA_PATH . "inc/__init.php"; //路由文件（初始化）
        require $bootstrap;
        exit;
    }

    public function payResult($params) //支付回调
    {
        global $_W;
        $_W["siteroot"] = str_replace(array("/addons/hello_banbanjia", "/payment/alipay"), array("", "", ""), $_W["siteroot"]);
        $_W["uniacid"] = $params["uniacid"];
        $record = pdo_get("hello_banbanjia_paylog", array("uniacid" => $_W["uniacid"], "order_sn" => $params["tid"]));
        if (empty($record)) {
            exit;
        }
        if ($record["addtime"] < strtotime("-1 month")) {
            exit;
        }
        $config = get_system_config();
        $_W["we7_hello_banbanjia"]["config"] = $config;
        if ($params["result"] == "success" && $params["from"] == "notify" || $params["from"] == "return" && in_array($params["type"], array("delivery", "finishMeal", "credit"))) {
            mload()->lmodel("order");
            $record["data"] = iunserializer($record["data"]);
            $params["prepay_id"] = $record["data"]["prepay_id"];
            pdo_update("hello_banbanjia_paylog", array("status" => 1, "paytime" => TIMESTAMP), array("id" => $record["id"]));

            if ($record['order_type'] == 'carry') {
                $order = pdo_get('hello_banbanjia_carry_order', array('id' => $record['order_id'], 'uniacid' => $_W['uniacid']));
                if (!empty($order) && !$order['is_pay']) {
                    $data = array(
                        'order_channel' => $params['channel'],
                        'pay_type' => $params['type'],
                        'final_feee' => $params['card_fee'],
                        'is_pay' => 1,
                        'paytime' => TIMESTAMP,
                        'out_trade_no' => $params['uniontid'],
                        'transaction_id' => $params['transaction_id']
                    );
                    if (!empty($params["prepay_id"])) {
                        $data["data"] = iunserializer($order["data"]);
                        $data["data"]["prepay_id"] = $params["prepay_id"];
                        $data["data"]["prepay_times"] = 3;
                        $data["data"] = iserializer($data["data"]);
                    }
                    pdo_update("hello_banbanjia_carry_order", $data, array("id" => $order["id"], "uniacid" => $_W["uniacid"]));
                    carry_order_status_update($order['id'], 'pay');
                    carry_order_status_update($order['id'], 'dispatch');
                }
            }
        }

        $routers = array( //结束跳转路由（跳转到订单详情）
            "wap" => array(
                "carry" => imurl('mall/order/carry/detail', array('id' => $record['order_id']), true),
            ),
            "wxapp" => array(
                "carry" => imurl('mall/order/carry/detail', array('id' => $record['order_id']), true),
            ),
        );
        $from = $ochannel = 'wechat';
        if ($params["channel"] == "wap" && $params["type"] == "alipay") {
            $from = "vue";
            $ochannel = "owap";
        }
        if ($params["from"] == "return") {
            $url = $routers[$from][$record["order_type"]];
            if (in_array($params["type"], array("credit"))) {
                imessage("下单成功", $url, "success");
            } else {
                header("location:" . $url);
                exit;
            }
        }
    }
}
