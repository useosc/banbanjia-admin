<?php
defined("IN_IA") or exit("Access Denied");

function diypage_menu($id)
{
    global $_W;
    $menu = pdo_fetch("SELECT * FROM " . tablename("hello_banbanjia_diypage_menu") . " WHERE id = :id and uniacid = :uniacid", array(":id" => $id, ":uniacid" => $_W["uniacid"]));
    if (!empty($menu)) {
        $menu["data"] = json_decode(base64_decode($menu["data"]), true);
    }
    return $menu;
}

function diypage_menus($version = 1)
{
    global $_W;
    $menu = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_diypage_menu") . " WHERE uniacid = :uniacid and version = :version", array(":uniacid" => $_W['uniacid'], ":version" => $version));
    return $menu;
}
