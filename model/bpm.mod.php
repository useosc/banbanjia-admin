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

/**
 * 获取当前用户可新建的订单
 * url: GET /api/1.0/{workspace}/case/start-cases
 */
function getBpmStartCases()
{
    $bpm = new Bpm();
    $accessToken = $bpm->userAccessToken();
    $url = "{$bpm->apiBaseUrl}/case/start-cases?access_token={$accessToken}";
    $cases = ihttp_get($url);
    return $cases;
}

/**
 * 流转到下一路由
 * url: PUT /api/1.0/{workspace}/cases/{app_uid}/route-case
 */
function routeBpmCase($params = array())
{
    $bpm = new Bpm();
    $accessToken = $bpm->userAccessToken();
    $url = "{$bpm->apiBaseUrl}/cases/{$params['app_uid']}/route-case";
    $params = array_filter($params, function ($v) {
        return !empty($v);
    });
    $params['access_token'] = $accessToken;
    $status = ihttp_put($url, $params);
    return $status;
}

/**
 * 获取任务内容
 * url: GET /api/1.0/{workspace}/cases/dyform
 */
function getBpmCaseContent($params = array())
{
    $bpm = new Bpm();
    $accessToken = $bpm->userAccessToken();
    $url = "{$bpm->apiBaseUrl}/cases/content?access_token={$accessToken}&";
    $params = array_filter($params, function ($v) {
        return !empty($v);
    });
    $params = http_build_query($params);
    $url .= $params;
    $cases = ihttp_get($url);
    return $cases;
}

//admin

/**
 * 创建新用户
 * POST /api/1.0/{workspace}/user
 */
function createBpmUsers($params = array())
{
    $bpm = new Bpm();
    $accessToken = $bpm->userAccessToken();
    $url = "{$bpm->apiBaseUrl}/user";
    $params = array_filter($params, function ($v) {
        return !empty($v);
    });
    $params['access_token'] = $accessToken;
    $users = ihttp_post($url, $params);
    return $users;
}

/**
 * 创建新空间
 * url: POST /api/1.0/{workspace}/system/site
 */
function createBpmWorkspace($params = array())
{
    $bpm = new Bpm();
    $accessToken = $bpm->userAccessToken();
    $url = "{$bpm->apiBaseUrl}/system/site";
    $params = array_filter($params, function ($v) {
        return !empty($v);
    });
    $params['access_token'] = $accessToken;
    $site = ihttp_post($url, $params);
    return $site;
}

/**
 * 初始化空间
 * url: POST
 */
function initBpmWorkspace($params = array())
{
    
}