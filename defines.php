<?php

defined("IN_IA") or exit("Access Denied");
!defined("WE7_BANBANJIA_PATH") and define("WE7_BANBANJIA_PATH", IA_ROOT . "/addons/hello_banbanjia/");
!defined("WE7_BANBANJIA_PLUGIN_PATH") and define("WE7_BANBANJIA_PLUGIN_PATH", WE7_BANBANJIA_PATH . "plugin/");
!defined("WE7_BANBANJIA_URL") and define("WE7_BANBANJIA_URL", $_W["siteroot"] . "addons/hello_banbanjia/");
!defined("WE7_BANBANJIA_URL_NOHTTPS") and define("WE7_BANBANJIA_URL_NOHTTPS", str_replace("https://", "http://", $_W["siteroot"]) . "addons/hello_banbanjia/");
!defined("WE7_BANBANJIA_STATIC") and define("WE7_BANBANJIA_STATIC", WE7_BANBANJIA_URL . "/static/");
!defined("WE7_BANBANJIA_LOCAL") and define("WE7_BANBANJIA_LOCAL", "../addons/hello_banbanjia/");
!defined("WE7_BANBANJIA_DEBUG") and define("WE7_BANBANJIA_DEBUG", "1");
!defined("WE7_BANBANJIA_ISHTTPS") and define("WE7_BANBANJIA_ISHTTPS", strexists($_W["siteroot"], "https://"));
define("IREGULAR_EMAIL", "/\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*/i");
define("IREGULAR_MOBILE", "/^[01][3456789][0-9]{9}\$/");
define("IREGULAR_PASSWORD", "/[0-9]+[a-zA-Z]+[0-9a-zA-Z]*|[a-zA-Z]+[0-9]+[0-9a-zA-Z]*/");