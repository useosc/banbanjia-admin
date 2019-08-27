<?php
defined("IN_IA") or exit("Access Denied");
load()->func('communication');
class WxAccount
{
    protected $acc = NULL;
    public function __construct($account = '')
    {
        global $_W;
        if(empty($account)){
            $account = $_W['acid'];
        }
        
    }
}