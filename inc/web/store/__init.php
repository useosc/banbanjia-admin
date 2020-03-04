<?php
defined('IN_IA') or exit('Access Denied');
global $_W;
global $_GPC;
if (0 < $_W["we7_hello_banbanjia"]["sid"]) {
    $_W['sid'] = $_W["we7_hello_banbanjia"]["sid"];
    $_W["we7_hello_banbanjia"]["store"] = store_fetch($_W["we7_hello_banbanjia"]["sid"]);
    $_W['store'] = $_W["we7_hello_banbanjia"]["store"];
}