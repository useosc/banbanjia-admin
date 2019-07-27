<?php
defined("IN_IA") or exit("Access Denied");

function icheckauth($force = true) //鉴权
{
    global $_W;
    global $_GPC;
    load()->model("mc");
    $_W["member"] = array();

    if (defined("IN_WXAPP")) {
        //统一用户
        if(empty($member)){
            $member = get_member($_W["openid"],"openid_wxapp");
        }
    }
}
