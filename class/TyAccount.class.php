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
        if (empty($acidOrAccount)) {
            $acidOrAccount = $_W["acid"];
        }
        if (is_array($acidOrAccount)) {
            $account = $acidOrAccount;
        } else {
            $wxapp = get_plugin_config("wxapp.basic");
            $account = array("key" => $wxapp["key"], "secret" => $wxapp["secret"]);
        }
        mload()->lclass("wxapp");
        return new Wxapp($account);
    }
}

?>