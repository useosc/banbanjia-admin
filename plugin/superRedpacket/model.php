<?php
defined("IN_IA") or exit("Access Denied");
function superRedpacket_cron()
{
    global $_W;
    pdo_query("update " . tablename("hello_banbanjia_superredpacket") . " set status = 0 where uniacid = :uniacid and status = 1 and type = :type and (endtime < :time or starttime > :time)", array(":uniacid" => $_W["uniacid"], ":type" => "share", ":time" => TIMESTAMP));
    pdo_query("update " . tablename("hello_banbanjia_superredpacket") . " set status = 1 where uniacid = :uniacid and status = 0 and type = :type and (endtime > :time and starttime < :time)", array(":uniacid" => $_W["uniacid"], ":type" => "share", ":time" => TIMESTAMP));
    return true;
}