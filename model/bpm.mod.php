<?php
// error_reporting(E_ALL);
defined("IN_IA") or exit("Access Denied");
mload()->lclass('bpm');

/**
 * 获取用户列表
 * url: /api/1.0/{workspace}/users?filter={filter}&start={start}&limit={limit}& status={status}
 */
function getBpmUsers($params = array())
{
    $bpm = new Bpm();
    $accessToken = $bpm->getAccessToken();
    $url = "{$bpm->apiBaseUrl}/users?access_token={$accessToken}&";
    $params = array_filter($params, function ($v) {
        return !empty($v);
    });
    $params = http_build_query($params);
    $url .= $params;
    $users = ihttp_get($url);
    return $users;
}

/**
 * 获取当前用户的订单
 * url: /api/1.0/{workspace}/cases
 */
function getBpmCases($params = array())
{ 
    $bpm = new Bpm();
    $accessToken = $bpm->userAccessToken();
    $url = "{$bpm->apiBaseUrl}/cases?access_token={$accessToken}&";
    $params = array_filter($params, function ($v) {
        return !empty($v);
    });
    $params = http_build_query($params);
    $url .= $params;
    $cases = ihttp_get($url);
    return $cases;
}
