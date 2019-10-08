<?php
// ini_set("display_errors", "1");
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
mload()->lmodel('deliveryer');
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if ($op == "list") {
    $_W["page"]["title"] = "搬运工";
    $condition = " WHERE uniacid = :uniacid and status = 1";
    $params[":uniacid"] = $_W["uniacid"];
    $work_status = isset($_GPC["work_status"]) ? intval($_GPC["work_status"]) : -1;
    if (-1 < $work_status) {
        $condition .= " and work_status = :work_status";
        $params[":work_status"] = $work_status;
    }
    $carry_num = intval($_GPC["carry_num"]);
    if ($carry_num == 1) {
        $condition .= " and collect_max_carry > 0 and order_carry_num >= collect_max_carry";
    } else {
        if ($carry_num == 2) {
            $condition .= " and (collect_max_carry = 0 or (collect_max_carry > 0 and order_carry_num < collect_max_carry))";
        }
    }
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and (title like '%" . $keyword . "%' or nickname like '%" . $keyword . "%' or mobile like '%" . $keyword . "%')";
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("hello_banbanjia_deliveryer") . $condition, $params);
    $data = pdo_fetchall("SELECT * FROM " . tablename("hello_banbanjia_deliveryer") . $condition . " ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
    foreach ($data as &$row) {
        $row["auth_info"] = iunserializer($row["auth_info"]);
        $row["extra"] = iunserializer($row["extra"]);
        $row["permit_transfer"] = iunserializer($row["permitit_transfer"]);
    }
    include itemplate("deliveryer/plateform");
}
if ($op == "post") {
    $_W["page"]["title"] = "搬运工信息";
    $id = intval($_GPC["id"]);
    if ($_W["ispost"]) {
        $mobile = trim($_GPC["mobile"]);
        if (!is_validMobile($mobile)) {
            imessage(error(-1, "手机号格式错误"), "", "ajax");
        }
        $is_exist = pdo_fetchcolumn("select id from " . tablename("hello_banbanjia_deliveryer") . " where uniacid = :uniacid and mobile = :mobile and id != :id", array(":uniacid" => $_W["uniacid"], ":mobile" => $mobile, ":id" => $id));
        if (!empty($is_exist)) {
            imessage(error(-1, "该手机号已绑定其他配送员, 请更换手机号"), "", "ajax");
        }
        $openid = trim($_GPC["wechat"]["openid"]);
        if (!empty($openid)) {
            $is_exist = pdo_fetchcolumn("select id from " . tablename("hello_banbanjia_deliveryer") . " where uniacid = :uniacid and openid = :openid and id != :id", array(":uniacid" => $_W["uniacid"], ":openid" => $openid, ":id" => $id));
            if (!empty($is_exist)) {
                imessage(error(-1, "该微信信息已绑定其他配送员, 请更换微信信息"), "", "ajax");
            }
        }
        $openid_wxapp = trim($_GPC["wechat"]["openid_wxapp"]);
        if (!empty($openid_wxapp)) {
            $is_exist = pdo_fetchcolumn("select id from " . tablename("hello_banbanjia_deliveryer") . " where uniacid = :uniacid and openid_wxapp = :openid_wxapp and id != :id", array(":uniacid" => $_W["uniacid"], ":openid_wxapp" => $openid_wxapp, ":id" => $id));
            if (!empty($is_exist)) {
                imessage(error(-1, "该微信信息已绑定其他配送员, 请更换微信信息"), "", "ajax");
            }
        }
        $data = array("uniacid" => $_W["uniacid"], "mobile" => $mobile, "title" => trim($_GPC["title"]), "openid" => $openid, "openid_wxapp" => trim($_GPC["wechat"]["openid_wxapp"]), "nickname" => trim($_GPC["wechat"]["nickname"]), "avatar" => trim($_GPC["wechat"]["avatar"]), "sex" => trim($_GPC["sex"]), "age" => intval($_GPC["age"]), "is_errander" => intval($_GPC["is_errander"]), "is_takeout" => intval($_GPC["is_takeout"]), "collect_max_takeout" => intval($_GPC["collect_max_takeout"]), "collect_max_errander" => intval($_GPC["collect_max_errander"]), "permit_cancel" => iserializer($_GPC["permit_cancel"]), "permit_transfer" => iserializer($_GPC["permit_transfer"]), "fee_getcash" => iserializer($_GPC["fee_getcash"]));
        $deliveryer_takeout_fee_type = intval($_GPC["deliveryer_takeout_fee_type"]);
        $deliveryer_takeout_fee = 0;
        if ($deliveryer_takeout_fee_type == 1) {
            $deliveryer_takeout_fee = floatval($_GPC["deliveryer_takeout_fee_1"]);
        } else {
            if ($deliveryer_takeout_fee_type == 2) {
                $deliveryer_takeout_fee = floatval($_GPC["deliveryer_takeout_fee_2"]);
            } else {
                if ($deliveryer_takeout_fee_type == 3) {
                    $deliveryer_takeout_fee = array("start_fee" => floatval($_GPC["deliveryer_takeout_fee_3"]["start_fee"]), "start_km" => floatval($_GPC["deliveryer_takeout_fee_3"]["start_km"]), "pre_km" => floatval($_GPC["deliveryer_takeout_fee_3"]["pre_km"]), "max_fee" => floatval($_GPC["deliveryer_takeout_fee_3"]["max_fee"]));
                } else {
                    if ($deliveryer_takeout_fee_type == 4) {
                        $deliveryer_takeout_fee = floatval($_GPC["deliveryer_takeout_fee_4"]);
                    }
                }
            }
        }
        $deliveryer_errander_fee_type = intval($_GPC["deliveryer_errander_fee_type"]);
        $deliveryer_errander_fee = 0;
        if ($deliveryer_errander_fee_type == 1) {
            $deliveryer_errander_fee = floatval($_GPC["deliveryer_errander_fee_1"]);
        } else {
            if ($deliveryer_errander_fee_type == 2) {
                $deliveryer_errander_fee = floatval($_GPC["deliveryer_errander_fee_2"]);
            } else {
                if ($deliveryer_errander_fee_type == 3) {
                    $deliveryer_errander_fee = array("start_fee" => floatval($_GPC["deliveryer_errander_fee_3"]["start_fee"]), "start_km" => floatval($_GPC["deliveryer_errander_fee_3"]["start_km"]), "pre_km" => floatval($_GPC["deliveryer_errander_fee_3"]["pre_km"]), "max_fee" => floatval($_GPC["deliveryer_errander_fee_3"]["max_fee"]));
                }
            }
        }
        $delivery_fee = array("takeout" => array("deliveryer_fee_type" => $deliveryer_takeout_fee_type, "deliveryer_fee" => $deliveryer_takeout_fee), "errander" => array("deliveryer_fee_type" => $deliveryer_errander_fee_type, "deliveryer_fee" => $deliveryer_errander_fee));
        $data["fee_delivery"] = iserializer($delivery_fee);
        if ($_W["is_agent"]) {
            $data["agentid"] = intval($_GPC["agent_id"]);
        }
        if (!$id) {
            $data["password"] = trim($_GPC["password"]) ? trim($_GPC["password"]) : imessage(error(-1, "登录密码不能为空"), "", "ajax");
            $length = strlen($data["password"]);
            if ($length < 8 || 20 < $length) {
                imessage(error(-1, "请输入8-20密码"), referer(), "ajax");
            }
            if (!preg_match(IREGULAR_PASSWORD, $data["password"])) {
                imessage(error(-1, "密码必须由数字和字母组合"), referer(), "ajax");
            }
            if ($data["password"] != trim($_GPC["repassword"])) {
                imessage(error(-1, "两次密码输入不一致"), referer(), "ajax");
            }
            $data["extra"] = iserializer(array("accept_wechat_notice" => 1, "accept_voice_notice" => 1));
            $data["salt"] = random(6);
            $data["token"] = random(32);
            $data["password"] = md5(md5($data["salt"] . $data["password"]) . $data["salt"]);
            $data["addtime"] = TIMESTAMP;
            pdo_insert("hello_banbanjia_deliveryer", $data);
            $id = pdo_insertid();
            deliveryer_all(true);
            mlog(4000, $id, "平台添加配送员");

            imessage(error(0, "添加配送员成功"), iurl("deliveryer/plateform/post", array("id" => $id)), "ajax");
        } else {
            $password = trim($_GPC["password"]);
            if (!empty($password)) {
                $length = strlen($password);
                if ($length < 8 || 20 < $length) {
                    imessage(error(-1, "请输入8-20密码"), referer(), "ajax");
                }
                if (!preg_match(IREGULAR_PASSWORD, $password)) {
                    imessage(error(-1, "密码必须由数字和字母组合"), referer(), "ajax");
                }
                if ($password != trim($_GPC["repassword"])) {
                    imessage(error(-1, "两次密码输入不一致"), referer(), "ajax");
                }
                $data["salt"] = random(6);
                $data["password"] = md5(md5($data["salt"] . $password) . $data["salt"]);
            }
            pdo_update("hello_banbanjia_deliveryer", $data, array("uniacid" => $_W["uniacid"], "id" => $id));
            if ($data["is_errander"] == 0 && check_plugin_permit("errander")) {
                mload()->model("plugin");
                pload()->model("errander");
                errander_category_deliveryer_reset($id);
            }
            deliveryer_all(true);
            mlog(4001, $id);
            imessage(error(0, "编辑配送员成功"), iurl("deliveryer/plateform/post", array("id" => $id)), "ajax");
        }
    }
    $deliveryer = pdo_get("hello_banbanjia_deliveryer", array("uniacid" => $_W["uniacid"], "id" => $id));
    if (!empty($deliveryer)) {
        $deliveryer["permit_cancel"] = iunserializer($deliveryer["permit_cancel"]);
        $deliveryer["permit_transfer"] = iunserializer($deliveryer["permit_transfer"]);
        $deliveryer["fee_getcash"] = iunserializer($deliveryer["fee_getcash"]);
        $deliveryer["fee_delivery"] = iunserializer($deliveryer["fee_delivery"]);
    }
    include itemplate("deliveryer/plateform");
}
if ($op == 'permit') { //接单权限
    $deliveryerId = intval($_GPC['id']);
    $fields = trim($_GPC['fields']);
    $value = intval($_GPC['value']) == 1 ? 0 : 1;
    pdo_update('hello_banbanjia_deliveryer', array($fields => $value), array('uniacid' => $_W['uniacid'], 'id' => $deliveryerId));
    imessage(error(0, ''), '', 'ajax');
}
