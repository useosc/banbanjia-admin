<?php
// ini_set("display_errors", "1"); //显示出错信息
// error_reporting(E_ALL ^ E_NOTICE);
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
global $_W;
global $_GPC;
$ta = trim($_GPC['ta']) ? trim($_GPC['ta']) : 'rules';
if ($ta == 'rules') {
    $_W['page']['title'] = '搬运设置';
    $config_carry = get_system_config('carry');
    if ($_W['ispost']) {
        if ($_GPC['form_type'] == 'order_setting') {
            $carry = array(
                'pay_time_limit' => intval($_GPC['pay_time_limit']),
                'handle_time_limit' => intval($_GPC['handle_time_limit']),
                'auto_success_hours' => intval($_GPC['auto_success_hours']),
                'carry_before_limit' => intval($_GPC['carry_before_limit']),
                'carry_timeout_limit' => intval($_GPC['carry_timeout_limit']),
                'dispatch_mode' => intval($_GPC['dispatch_mode']),
                'deliveryer_collect_max' => intval($_GPC['deliveryer_collect_max']),
                'over_collect_max_notify' => intval($_GPC['over_collect_max_notify']),
                'deliveryer_transfer_status' => intval($_GPC['deliveryer_transfer_status']),
                'deliveryer_transfer_max' => intval($_GPC['deliveryer_transfer_max']),
                "deliveryer_transfer_reason" => explode("\n", trim($_GPC["deliveryer_transfer_reason"])),
                "deliveryer_cancel_reason" => explode("\n", trim($_GPC["deliveryer_cancel_reason"])),
                'deliveryer_fee' => floatval($_GPC['deliveryer_fee'])
            );
            // var_dump($carry);exit;
            $carry = array_merge($config_carry, $carry);
            set_system_config("carry", $carry);
            set_config_text("搬家服务用户协议", "agreement_carry", htmlspecialchars_decode($_GPC['agreement']));
        } else if ($_GPC['form_type'] == 'price_setting') {
            $options = array();
            foreach ($_GPC['options']['name'] as $key => $val) {
                $val = trim($val);
                $price = floatval($_GPC['options']['price'][$key]);
                if (empty($val) || empty($price)) {
                    continue;
                }
                $options[] = array('name' => $val, 'price' => $price);
            }
            $carry = array(
                'carry_fee' =>  array(
                    "start_fee" => floatval($_GPC['carry_fee']['start_fee']),
                    "start_km" => floatval($_GPC["carry_fee"]["start_km"]),
                    "pre_km" => floatval($_GPC["carry_fee"]["pre_km"]),
                    "max_fee" => floatval($_GPC["carry_fee"]["max_fee"])
                ),
                'volume_status' => intval($_GPC['volume_status']),
                'carry_fee_basic' => floatval($_GPC['carry_fee_basic']),
                'over_cube' => floatval($_GPC['over_cube']),
                'pre_cube_fees' => floatval($_GPC['pre_cube_fees']),
                'options' => $options,
            );
            $carry = array_merge($config_carry, $carry);
            set_system_config("carry", $carry);
        } else {
            $data['credit1'] = array(
                "status" => intval($_GPC['credit1']['status']),
                "grant_type" => intval($_GPC['credit1']['grant_type'])
            );
            $data['credit1']['grant_num'] = $data['credit1']['grant_type'] == 1 ? intval($_GPC['credit1']['grant_num_1']) : intval($_GPC['credit1']['grant_num_2']);
            set_system_config("carry.credit", $data);
        }
        imessage(error(0, "设置搬运服务参数成功"), 'refresh', 'ajax');
    }
    if (!empty($config_carry["deliveryer_transfer_reason"])) {
        $config_carry["deliveryer_transfer_reason"] = implode("\n", $config_carry["deliveryer_transfer_reason"]);
    }
    if (!empty($config_carry["deliveryer_cancel_reason"])) {
        $config_carry["deliveryer_cancel_reason"] = implode("\n", $config_carry["deliveryer_cancel_reason"]);
    }
    $agreement_carry = get_config_text("agreement_carry");
    include itemplate('carry/rules');
}
