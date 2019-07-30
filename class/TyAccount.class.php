<?php
defined("IN_IA") or exit("Access Denied");
abstract class TyAccount
{
    public static function create($acidOrAccount = "", $type = "wechat")
    {
        global $_W;
        if ($type != "wxapp") {
            mload()->lclass("wxaccount");
            $acc = new WxAccount($acidOrAccount);
            return $acc;
        }
    }
}
