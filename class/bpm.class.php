<?php
defined("IN_IA") or exit("Access Denied");
load()->func("communication");

class Bpm
{
    public $account; //软件appid
    public $userAccount;
    // public $workspace = 'banbanjia';
    public $workspace = 'test1';
    public $api_version = '1.0'; //rest api version
    public $bpm_url = 'https://bpm.useosc.com';

    public $apiBaseUrl; //rest api base url

    // public function __construct($account = array('key' => 'NSRRYUIDVVPMTHXDKTHMPFQCMLCMPOHC', 'secret' => '2899097065dc6398dcc9200095664917'))
    public function __construct($account = array('key' => 'KPTYOAEFSTPPDJRXJZXUWYFZNWPXFOIP', 'secret' => '1366580955dd8e9f1872d01028461614'))
    {
        global $_W;
        if (!is_array($account)) {
            $account = '';
        }
        $this->account = $account;

        $this->userAccount = array('username' => 'gendan01', 'password' => '123456'); //用户信息
        // $this->userAccount = array('username' => 'admin', 'password' => '123456'); //用户信息

        $this->apiBaseUrl = $this->bpm_url . '/' . 'api' . '/' . $this->api_version . '/' . $this->workspace;
    }

    public function requestApi($url, $post = '')
    {
        $response = ihttp_request($url, $post);
        $result = @json_decode($response['content'], true);
        if (is_error($response)) {
            return error($result["errcode"], "访问bpm平台接口失败, 错误详情: " . $this->errorCode($result["errcode"]));
        }
        if (empty($result)) {
            return $response;
        }
        if (!empty($result["errcode"])) {
            return error($result["errcode"], "访问bpm平台接口失败, 错误: " . $result["errmsg"] . ",错误详情：" . $this->errorCode($result["errcode"]));
        }
        return $result;
    }

    public function getAccessToken()
    {
        $cachekey = "bpmtoken:" . $this->account['key'];
        $cache = cache_load($cachekey);
        if (!empty($cache) && !empty($cache['token']) && TIMESTAMP < $cache['expire']) {
            $this->account['access_token'] = $cache;
            return $cache['token'];
        }
        if (empty($this->account['key']) || empty($this->account['secret'])) {
            return error("-1", "没有bpm系统的appid或appsecret!");
        }
        $url = "{$this->bpm_url}/{$this->workspace}/oauth2/token";
        $postData = array(
            'grant_type' => 'client_credentials',
            'client_id' => $this->account['key'],
            'client_secret' => $this->account['secret'],
            'scope' => '*'
        );
        $response = $this->requestApi($url, $postData);
        $record = array();
        $record['token'] = $response['access_token'];
        $record["expire"] = TIMESTAMP + $response["expires_in"] - 200;
        $this->account["access_token"] = $record;
        cache_write($cachekey, $record);
        return $record['token'];
    }

    //用户accessToken
    public function userAccessToken()
    {
        $cachekey = "usertoken:" . $this->userAccount['username'];
        $cache = cache_load($cachekey);
        if (!empty($cache) && !empty($cache['token']) && TIMESTAMP < $cache['expire']) {
            $this->userAccount['access_token'] = $cache;
            // echo $cache['token'];exit;
            return $cache['token'];
        }
        if (empty($this->userAccount['username']) || empty($this->userAccount['password'])) {
            return error("-1", "没有绑定用户的账号或密码");
        }
        $url = "{$this->bpm_url}/{$this->workspace}/oauth2/token";
        $postData = array(
            'grant_type' => 'password',
            'client_id' => $this->account['key'],
            'client_secret' => $this->account['secret'],
            'scope' => '*',
            'username' => $this->userAccount['username'],
            'password' => $this->userAccount['password']
        );
        $response = $this->requestApi($url, $postData);
        $record = array();
        $record['token'] = $response['access_token'];
        $record["expire"] = TIMESTAMP + $response["expires_in"] - 200;
        $this->userAccount["access_token"] = $record;
        cache_write($cachekey, $record);
        return $record['token'];
    }

    


}
