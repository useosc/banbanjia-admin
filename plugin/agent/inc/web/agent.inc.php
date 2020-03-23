<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "list";
if($op == 'list'){
    $_W["page"]["title"] = "代理列表";
    $condition = " where uniacid = :uniacid";
    $params = array(":uniacid" => $_W["uniacid"]);
    $keyword = trim($_GPC["keyword"]);
    if (!empty($keyword)) {
        $condition .= " and (title like '%" . $keyword . "%' or area like '%" . $keyword . "%')";
    }
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 15;
    $total = pdo_fetchcolumn("select count(*) from " . tablename("hello_banbanjia_agent") . $condition, $params);
    $agents = pdo_fetchall("select * from " . tablename("hello_banbanjia_agent") . $condition . " order by id desc limit " . ($pindex - 1) * $psize . "," . $psize, $params);
    $pager = pagination($total, $pindex, $psize);
}
if($op == 'post'){
    $_W["page"]["title"] = "代理设置";
    $id = intval($_GPC["id"]);
    if(0 < $id) {
        $agent = pdo_get("hello_banbanjia_agent", array("id" => $id));
        $agent["data"] = iunserializer($agent["data"]);
        $agent["geofence"] = iunserializer($agent["geofence"]);
        $item = array("isChange" => 1, "delivery_areas" => $agent["geofence"]["areas"], "location_y" => $agent["geofence"]["map"]["lng"], "location_x" => $agent["geofence"]["map"]["lat"]);
    }else{
        $item["isChange"] = 1;
    }
    if($_W['ispost']){
        $mobile = trim($_GPC["mobile"]);
        if (!is_validMobile($mobile)) {
            imessage(error(-1, "手机号格式错误"), referer(), "ajax");
        }
        $is_exist = pdo_fetch("select id from " . tablename("hello_banbanjia_agent") . " where uniacid = :uniacid and id != :id and mobile = :mobile", array(":id" => $id, ":mobile" => $mobile, ":uniacid" => $_W["uniacid"]));
        if (!empty($is_exist)) {
            imessage(error(-1, "该手机号已被其他代理注册"), referer(), "ajax");
        }
        $area = trim($_GPC['area']);
        if(empty($area)){
            imessage(error(-1, "代理区域不能为空"), referer(), "ajax");
        }
        mload()->lclass("pinyin");
        $pinyin = new pinyin();
        $initial = $pinyin->getFirstPY($area);
        $initial = strtoupper(substr($initial, 0, 1));
        $data = array("uniacid" => intval($_W["uniacid"]), "title" => trim($_GPC["title"]), "realname" => trim($_GPC["realname"]), "mobile" => $mobile, "area" => $area, "initial" => $initial, "status" => intval($_GPC["status"]));
        $data["data"] = $agent["data"];
        $data["data"] = iserializer($data["data"]);
        $_GPC["areas"] = str_replace("&nbsp;", "#nbsp;", $_GPC["areas"]);
        $_GPC["areas"] = json_decode(str_replace("#nbsp;", "&nbsp;", html_entity_decode(urldecode($_GPC["areas"]))), true);
        foreach ($_GPC['areas'] as $key => &$val) {
            if(empty($val['path'])) {
                unset($_GPC['areas'][$key]);
            }
            $path = array();
            foreach($val['path'] as $row){
                $path[] = array($row["lng"], $row["lat"]);
            }
            $val["path"] = $path;
            unset($val["isAdd"]);
            unset($val["isActive"]);
        }
        $data["geofence"]["areas"] = $_GPC["areas"];
        $data["geofence"]["map"]["lat"] = trim($_GPC["map"]["lat"]);
        $data["geofence"]["map"]["lng"] = trim($_GPC["map"]["lng"]);
        $data["geofence"] = iserializer($data["geofence"]);
        if(0 < $id){
            $password = trim($_GPC["password"]);
            if(!empty($password)){
                $data['salt'] = random(6);
                $data["password"] = md5(md5($data["salt"] . $password) . $data["salt"]);
            }
            pdo_update("hello_banbanjia_agent",$data,array("id"=>$id,"uniacid"=>$_W['uniacid']));
        }else{
            $data["password"] = trim($_GPC["password"]) ? trim($_GPC["password"]) : imessage(error(-1, "密码不能为空"), "", "ajax");
            $data["salt"] = random(6);
            $data["password"] = md5(md5($data["salt"] . $data["password"]) . $data["salt"]);
            pdo_insert("hello_banbanjia_agent", $data);
            $agent_id = pdo_insertid();
            mlog(5000, $agent_id);
        }
        imessage(error(0, "编辑代理成功"), iurl("agent/agent/list"), "ajax");
    }
}
if ($op == "del") {
    $ids = $_GPC["id"];
    if (!is_array($ids)) {
        $ids = array($ids);
    }
    foreach ($ids as $id) {
        pdo_delete("hello_banbanjia_agent", array("id" => $id, "uniacid" => $_W["uniacid"]));
        mlog(5001, $id);
    }
    imessage(error(0, "删除代理成功"), "", "ajax");
}
if ($op == "status") {
    $id = intval($_GPC["id"]);
    $status = intval($_GPC["status"]);
    pdo_update("hello_banbanjia_agent", array("status" => $status), array("id" => $id, "uniacid" => $_W["uniacid"]));
    imessage(error(0, ""), "", "ajax");
}
if($op == 'set') {
    $_W['page']['title'] = '账户设置';
    $id = intval($_GPC['id']);
    $agent = pdo_get("hello_banbanjia_agent", array("uniacid" => $_W["uniacid"], "id" => $id));
    if (empty($agent)) {
        imessage(error(-1, "代理不存在或已删除"), "", "ajax");
    }
    $agent["fee"] = iunserializer($agent["fee"]);
    $gohome = $agent["fee"]["fee_gohome"];
    
}
include itemplate("agent");
