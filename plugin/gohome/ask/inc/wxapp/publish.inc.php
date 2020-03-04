<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$op = trim($_GPC["op"]) ? trim($_GPC["op"]) : "index";
icheckauth(true);
if ($op == "index") {
    $categorys = ask_get_categorys(array("status" => 1));
    $result = array("categorys" => $categorys);
    imessage(error(0, $result), "", "ajax");
}else{
    if($op == 'post'){
        $childid = intval($_GPC["childid"]);
        $id = $childid ? $childid : intval($_GPC["parentid"]);
        $category = ask_get_category($id);
        if(empty($category)){
            imessage(error(-1, "分类不存在"), "", "ajax");
        }
        $publish = json_decode(htmlspecialchars_decode($_GPC['publish']),true);
        if($_W['ispost']){
            $update = array("title" => trim($publish['title']), "content" => trim($publish["content"]));
            $update['uniacid'] = $_W['uniacid'];
            $update['agentid'] = $_W['agentid'];
            $update['uid'] = $_W['member']['uid'];
            $update["parentid"] = empty($category["parentid"]) ? $category["id"] : $category["parentid"];
            $update["childid"] = empty($category["parentid"]) ? 0 : $category["id"];
            $update["addtime"] = TIMESTAMP;

            if(!empty($publish['thumbs'])) {
                $update['thumbs'] = iserializer($publish['thumbs']);
            }
            
            $audit_status = intval($_config_plugin["ask"]["audit"]["new"]);
            $update["status"] = $audit_status == 1 ? 2 : 3;
        }
        pdo_insert("hello_banbanjia_ask_information",$update);
        $infor_id = pdo_insertid();

        // $result = array("category" => $category, "member" => array("realname" => $_W["member"]["realname"], "title" => $_W["member"]["title"]),"publish" => $information);
        $result = array("member" => array("nickname" => $_W["member"]["nickname"]));
        imessage(error(0, $result), "", "ajax");
        return 1;
    }
}