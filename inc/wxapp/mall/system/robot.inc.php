<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC["ta"]) ? trim($_GPC["ta"]) : "index";
if ($ta == 'answer') {
    $msg = trim($_GPC['msg']);
    $questions = pdo_getall("hello_banbanjia_service_robot_wiki", array("uniacid" => $_W['uniacid']));
    $answer = array("my" => false);
    // $answer = array("my" => 'false',"msg" =>"","type" => 1,"questionList"=>array(''));

    $threshold = 15;
    $shortest = -1;
    foreach ($questions as $question) {
        $lev = levenshtein($msg, $question['title']);
        if ($lev == 0) { //完全匹配
            $closest = $question;
            $shortest = 0;
            break;
        }
        if ($lev <= $threshold) {
            $questionList[] = $question['title'];
            $shortest = $lev;
        }
    }
    // 或者还没找到接近的单词
    if ($shortest < 0) {
        if (empty($questions)) {
            $questionList = '暂时没有知识库';
        }
        $rand_keys = array_rand($questions, 3);
        foreach ($rand_keys as $rand) {
            $questionList[] = $questions[$rand]['title'];
        }
        // $questionList = array_rand($questions,3);
        $shortest = $threshold + 1;
    }

    if ($shortest == 0) {
        $answer['msg'] = $closest['content'];
        $answer['type'] = -1;
        imessage(error(0, $answer), '', 'ajax');
    }
    if ($shortest <= $threshold) {
        $answer['msg'] = "根据您的问题,已为您匹配了下列问题";
        $answer['type'] = 2;
        $answer['questionList'] = $questionList;
    }
    if ($shortest > $threshold) {
        $answer['msg'] = "娜娜还在学习中,没能明白您的问题,您点击下方提交反馈与问题,我们会尽快人工处理";
        $answer['type'] = 0;
        $answer['questionList'] = $questionList;
    }
    imessage(error(0, $answer), '', 'ajax');
}



if ($ta == 'changeqt') {
    $questions = pdo_getall("hello_banbanjia_service_robot_wiki", array("uniacid" => $_W['uniacid']));
    if (empty($questions)) {
        $questionList = '暂时没有知识库';
    }
    $rand_keys = array_rand($questions, 3);
    foreach ($rand_keys as $rand) {
        $questionList[] = $questions[$rand]['title'];
    }
    imessage(error(0, $questionList), '', 'ajax');
}

if ($ta == 'feedback') {
    $publish = json_decode(htmlspecialchars_decode($_GPC['publish']),true);
    $update = array("contact" => trim($publish['contact']), "content" => trim($publish["content"]));
    if(!empty($publish['thumbs'])) {
        $update['thumbs'] = iserializer($publish['thumbs']);
    }
    $update['uniacid'] = $_W['uniacid'];
    $update["addtime"] = TIMESTAMP;
    pdo_insert("hello_banbanjia_service_feedback",$update);
    imessage(error(0, "感谢您的反馈，我们会尽快处理"), '', 'ajax');
}
