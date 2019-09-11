<?php
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
mload()->lmodel('order');
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'index';
if ($ta == 'index') {
    $data = array(
        'uniacid' => $_W['uniacid'],
        'uid' => $_W['member']['uid'],
        'type' => $_GPC['type'],
        'volume' => $_GPC['volume'],
        'data' => iserializer($room_goods),
        'addtime' => TIMESTAMP,
    );
    pdo_insert("hello_banbanjia_order_estimate", $data);
    $data['id'] = pdo_insertid();
    imessage('', '', 'ajax');
} else {
    if ($ta == 'price') {
        $data = array(
            'remark' => '备注',
            'time' => '09-07',
            'end_address' => array(
                'address' => '广东省广州市天河区车陂南(地铁站)',
                'location_x' => "23.115930",
                'location_y' => "113.389561"
            ),
            'goods_volume' => 20,
            'start_address' => array(
                'address' => '广东省广州市天河区天河公园',
                'location_x' => '23.128003',
                'location_y' => '113.366739'
            )
        );
        $condition = array();
        // $params = json_decode(htmlspecialchars_decode($_GPC['extra']), true);
        // if (!empty($params)) {
        //     //获取地址
        // }
        $condition = $data;
        $order = carry_order_calculate_delivery_fee($condition, intval($_GPC['is_calculate']));
        if (is_error($order)) {
            message($order, '', 'ajax');
        }
        $result = array('order' => $order);
        message(error(0, $result), '', 'ajax');
    }
}
