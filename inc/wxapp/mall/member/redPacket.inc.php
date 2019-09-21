<?php
defined("IN_IA") or exit("Access Denied");
global $_GPC;
global $_W;
icheckauth();
mload()->lmodel('redPacket');
redPacket_cron();
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "list";
if($ta == 'list'){
    $id = intval($_GPC["min"]);
    $condition = " where uniacid = :uniacid and uid = :uid";
    $params[":uniacid"] = $_W["uniacid"];
    $params[":uid"] = $_W["member"]["uid"];
    if (0 < $id) {
        $condition .= " and id < :id";
        $params[":id"] = $id;
    }
    $status = intval($_GPC["status"]) ? intval($_GPC["status"]) : 1;
    if ($status == 1) {
        $condition .= " and status = :status";
        $params[":status"] = $status;
    } else {
        $condition .= " and status > 1";
    }
    $redPackets = pdo_fetchall("select * from " . tablename("hello_banbanjia_activity_redpacket_record") . $condition . " order by id desc limit 6", $params, "id");
    if (!empty($redPackets)) {
        foreach ($redPackets as &$row) {
            $row["mobile"] = $_W["member"]["mobile"];
            $row["starttime"] = date("Y-m-d", $row["starttime"]);
            $row["endtime"] = date("Y-m-d", $row["endtime"]);
            // $row["time_cn"] = totime($row["times_limit"]);
            // if (!empty($row["time_cn"])) {
            //     $row["time_cn"] = "仅限" . $row["time_cn"] . "时段使用";
            // }
            // $row["category_cn"] = tocategory($row["category_limit"]);
            // if (!empty($row["category_cn"])) {
            //     $row["category_cn"] = "仅限" . $row["category_cn"] . "分类使用";
            // }
            // if (0 < $row["sid"]) {
            //     $row["title"] = $stores[$row["sid"]]["title"];
            //     $row["category_cn"] = "仅限" . $stores[$row["sid"]]["title"] . "门店使用";
            // }
            // $row["channel_cn"] = $channels[$row["channel"]];
        }
    }

    $redPackets = array_values($redPackets);
    $respon = array("errno" => 0, "message" => $redPackets, "min" => $min);
    imessage($respon, "", "ajax");
}