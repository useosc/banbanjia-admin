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
    {}
    public function doWebTest() //测试

    {
    }
    public function doWebWeb() //后台管理

    {
        ini_set("display_errors", "1"); //显示出错信息
        // error_reporting(E_ALL ^ E_NOTICE);
        $this->router(); //路由函数
    }

    public function router() //路由函数

    {
        $bootstrap = WE7_BANBANJIA_PATH . "inc/__init.php"; //路由文件（初始化）
        require $bootstrap;
        exit;
    }
}
