<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;

//获取节点数据
function getNodeDataArray($layoutData, $dxml)
{
    $wf_xml = new DOMDocument();
    $wf_xml->loadXML($dxml);
    $locX = 0;
    $locY = 0;

    $nodeDataArray = array();
    //起始节点
    $start_node = $wf_xml->getElementsByTagName('initial-actions')->item(0);
    $obj = array();
    $obj['key'] = $start_node->getAttribute("id");
    $obj['id'] = $start_node->getAttribute('id');
    $obj['text'] = '开始';
    $obj['category'] = 'Start';
    $obj['loc'] = $locX . ' ' . $locY;
    $nodeDataArray = node_push($nodeDataArray, $layoutData, $obj);

    //流程节点
    // foreach($wf_xml->)
}

// 将节点数据放到数组
function node_push($nodeDataArray, $layoutData, $obj)
{
    foreach ($layoutData as $row) {
        if ($obj['key'] == $row->key) {
            foreach ($row as $key => $value) {
                if ($key != 'text') {
                    $obj[$key] = $value;
                }
            }
            break;
        }
    }
    array_push($nodeDataArray, $obj);
    return $nodeDataArray;
}
