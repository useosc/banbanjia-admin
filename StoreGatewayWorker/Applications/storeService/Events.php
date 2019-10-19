<?php

/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);
use \GatewayWorker\Lib\Gateway;
use Workerman\Lib\Timer;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 新建一个类的静态成员，用来保存数据库实例
     */
    public static $db = null;
    public static $global = null;

    /**
     * 进程启动后初始化数据库连接
     */
    public static function onWorkerStart($worker)
    {
        if (empty(self::$db)) {
            self::$db = new \Workerman\MySQL\Connection('127.0.0.1', '3306', 'root', 'WDELI1995', 'banbanjia');
        }

        if (empty(self::$global)) {
            self::$global = new \GlobalData\Client('127.0.0.1:3207');
            // 客服列表
            if (is_null(self::$global->kfList)) {
                self::$global->kfList = [];
            }
            // 会员列表[动态的，这里面只是目前未被分配的会员信息]
            if (is_null(self::$global->userList)) {
                self::$global->userList = [];
            }
            // 会员以 uid 为key的信息简表,只有在用户退出的时候，才去执行修改
            if (is_null(self::$global->uidSimpleList)) {
                self::$global->uidSimpleList = [];
            }
            // 当天的累积接入值
            $key = date('Ymd') . 'total_in';
            if (is_null(self::$global->$key)) {
                self::$global->$key = 0;

                $oldKey = date('Ymd', strtotime('-1 day')); // 删除前一天的统计值
                unset(self::$global->$oldKey);
                unset($oldKey, $key);
            }
            // 成功接入值
            $key = date('Ymd') . 'success_in';
            if (is_null(self::$global->$key)) {
                self::$global->$key = 0;

                $oldKey = date('Ymd', strtotime('-1 day')); // 删除前一天的统计值
                unset(self::$global->$oldKey);
                unset($oldKey, $key);
            }
        }

        // // 定时统计数据
        // if (0 === $worker->id) {
        //     // 1分钟统计一次实时数据
        //     Timer::add(60 * 1, function () {
        //         self::writeLog(1);
        //     });
        //     // 40分钟写一次当前日期点数的log数据
        //     // Timer::add(60 * 40, function(){
        //     //     self::writeLog(2);
        //     // });
        // }
    }

    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    { }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    //
    /** $message = array(
     *      'type' => 'userInit',
     *      'uid'  => '1000',
     *      'sid'  => '1500',
     * );
     * $message = array(
     *      'type' => 'init',
     *      'uid'  => '1000',
     *      'sid'  => '1500',
     * );
     * */
    public static function onMessage($client_id, $message)
    {
        $message = json_decode($message, true);
        switch ($message['type']) {
                // 客服初始化
            case 'init':
                $kfList = self::$global->kfList;
                // 如果该客服未在内存中记录则记录
                if (!isset($kfList[$message['sid']]) || !array_key_exists($message['uid'], $kfList[$message['sid']])) {
                    do {
                        $newKfList = $kfList;
                        $newKfList[$message['sid']][$message['uid']] = [
                            'uid' => $message['uid'],
                            'name' => $message['name'],
                            'avatar' => $message['avatar'],
                            'client_id' => $client_id,
                            'task' => 0,
                            'user_info' => []
                        ];
                    } while (!self::$global->cas('kfList', $kfList, $newKfList));
                    unset($newKfList, $kfList);
                } else if (isset($kfList[$message['sid']][$message['uid']])) {
                    do {
                        $newKfList = $kfList;
                        $newKfList[$message['sid']][$message['uid']]['client_id'] = $client_id;
                    } while (!self::$global->cas('kfList', $kfList, $newKfList));
                    unset($newKfList, $kfList);
                }

                // 绑定 client_id 和 uid
                Gateway::bindUid($client_id, $message['sid'] . '-' . $message['uid']);
                // TODO 尝试拉取用户来服务 [二期规划]
                break;

                // 顾客初始化(顾客发送信息)
            case 'userInit';
                $userList = self::$global->userList;
                // 如果该顾客未在内存中记录则记录
                if (!isset($userList[$message['sid']]) || !array_key_exists($message['uid'], $userList[$message['sid']])) {
                    do {
                        $NewUserList = $userList;
                        $NewUserList[$message['sid']][$message['uid']] = [
                            'uid' => $message['uid'],
                            'name' => $message['name'],
                            'avatar' => $message['avatar'],
                            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
                            'sid' => $message['sid'],
                            'client_id' => $client_id
                        ];
                    } while (!self::$global->cas('userList', $userList, $NewUserList));
                    unset($NewUserList, $userList);

                    // 维护 UID对应的client_id 数组
                    do {
                        $old = $newList = self::$global->uidSimpleList;
                        $newList[$message['sid']][$message['uid']] = [
                            $client_id,
                            $message['sid']
                        ];
                    } while (!self::$global->cas('uidSimpleList', $old, $newList));
                    unset($old, $newList);

                    // 写入接入值
                    $key = date('Ymd') . 'total_in';
                    self::$global->$key = 0;
                    do {
                        $oldKey = date('Ymd', strtotime('-1 day')); // 删除前一天的统计值
                        unset(self::$global->$oldKey);
                    } while (!self::$global->increment($key));
                    unset($key);
                }

                // 绑定 client_id 和 uid
                Gateway::bindUid($client_id, $message['sid'] . '-' . $message['uid']);


                // 尝试分配新用户进入服务
                self::userOnlineTask($client_id, $message['sid']);
                break;
                // 聊天
            case 'chatMessage':
                $chatUid = $message['data']['sid'] . '-' . $message['data']['to_id'];
                $client = Gateway::getClientIdByUid($chatUid);
                if (!empty($client)) {
                    $chat_message = [
                        'message_type' => 'chatMessage',
                        'data' => [
                            'name' => $message['data']['from_name'],
                            'avatar' => $message['data']['from_avatar'],
                            'id' => $message['data']['from_id'],
                            'time' => date('H:i'),
                            'content' => htmlspecialchars($message['data']['content']),
                            'sid' => $message['data']['sid'],
                        ]
                    ];
                    Gateway::sendToUid($chatUid, json_encode($chat_message));
                    unset($chat_message);

                    // 聊天信息入库
                    // $serviceLog = [
                    //     'from_id' => $message['data']['from_id'],
                    //     'from_name' => $message['data']['from_name'],
                    //     'from_avatar' => $message['data']['from_avatar'],
                    //     'to_id' => $message['data']['to_id'],
                    //     'to_name' => $message['data']['to_name'],
                    //     'content' => $message['data']['content'],
                    //     'time_line' => time()
                    // ];

                    // self::$db->insert('ims_hello_banbanjia_service_chat_log')->cols($serviceLog)->query();
                    // unset($serviceLog);
                }
                break;
        }
    }

    /**
     * 有人进入执行分配
     * @param $client_id
     * @param $sid
     */
    private static function userOnlineTask($client_id, $sid)
    {
        $res = self::assignmentTask(self::$global->kfList, self::$global->userList, $sid, 10);

        if (1 == $res['code']) {

            while (!self::$global->cas('kfList', self::$global->kfList, $res['data']['4'])) { }; // 更新客服数据
            while (!self::$global->cas('userList', self::$global->userList, $res['data']['5'])) { }; // 更新会员数据

            // 通知会员发送信息绑定客服的id
            $noticeUser = [
                'message_type' => 'connect',
                'data' => [
                    'kf_id' => $res['data']['0'],
                    'kf_name' => $res['data']['1'],
                    'sid' => $res['data']['6']

                ]
            ];
            Gateway::sendToClient($client_id, json_encode($noticeUser));
            unset($noticeUser);

            // 自动应答(欢迎语)
            $hello = [
                'message_type' => 'helloMessage',
                'data' => [
                    'name' => $res['data']['1'],
                    'avatar' => '',
                    'uid' => $res['data']['0'],
                    'time' => date('H:i'),
                    'content' => htmlspecialchars('欢迎')
                ]
            ];
            Gateway::sendToClient($client_id, json_encode($hello));
            unset($hello);
            unset($sayHello);

            // 通知客服端绑定会员的信息
            $noticeKf = [
                'message_type' => 'connect',
                'data' => [
                    'user_info' => $res['data']['3']
                ]
            ];
            Gateway::sendToClient($res['data']['2'], json_encode($noticeKf));
            unset($noticeKf);

            // // 服务信息入库
            // $serviceLog = [
            //     'user_id' => $res['data']['3']['id'],
            //     'client_id' => $res['data']['3']['client_id'],
            //     'user_name' => $res['data']['3']['name'],
            //     'user_ip' => $res['data']['3']['ip'],
            //     'user_avatar' => $res['data']['3']['avatar'],
            //     'kf_id' => intval(ltrim($res['data']['0'], 'KF')),
            //     'start_time' => time(),
            //     'group_id' => $group,
            //     'end_time' => 0
            // ];

            // self::$db->insert('ims_hello_banbanjia_service_log')->cols($serviceLog)->query();
            // unset($serviceLog);

            // 写入接入值
            $key = date('Ymd') . 'success_in';
            self::$global->$key = 0;
            do {
                $oldKey = date('Ymd', strtotime('-1 day')); // 删除前一天的统计值
                unset(self::$global->$oldKey);
            } while (!self::$global->increment($key));
            unset($key);
        } else {

            $waitMsg = '';
            switch ($res['code']) {

                case -1:
                    $waitMsg = '暂时没有客服上班,请稍后再咨询。';
                    break;
                case -2:
                    break;
                case -3:
                    break;
                case -4:
                    $number = count(self::$global->userList);
                    $waitMsg = '您前面还有 ' . $number . ' 位会员在等待。';
                    break;
            }

            $waitMessage = [
                'message_type' => 'wait',
                'data' => [
                    'content' => $waitMsg,
                ]
            ];

            Gateway::sendToClient($client_id, json_encode($waitMessage));
            unset($waitMessage);
        }
    }


    /**
     * 给客服分配会员【均分策略】
     * @param $kfList
     * @param $userList
     * @param $sid
     * @param $total
     */
    private static function assignmentTask($kfList, $userList, $sid, $total)
    {
        // 没有客服上线
        if (empty($kfList) || empty($kfList[$sid])) {
            return ['code' => -1];
        }

        // 没有待分配的会员
        if (empty($userList) || empty($userList[$sid])) {
            return ['code' => -2];
        }

        // 未设置每个客服可以服务多少人
        if (0 == $total) {
            return ['code' => -3];
        }

        // 查看该组的客服是否在线
        if (!isset($kfList[$sid])) {
            return ['code' => -1];
        }

        $kf = $kfList[$sid];
        $user = $userList[$sid];

        $user = array_shift($user);

        $kf = array_shift($kf);
        $min = $kf['task'];
        $flag = $kf['uid'];

        foreach ($kfList[$sid] as $key => $vo) {
            if ($vo['task'] < $min) {
                $min = $vo['task'];
                $flag = $key;
            }
        }
        unset($kf);

        // 需要排队了
        if ($kfList[$sid][$flag]['task'] == $total) {
            return ['code' => -4];
        }

        $kfList[$sid][$flag]['task'] += 1;
        array_push($kfList[$sid][$flag]['user_info'], $user['client_id']); // 被分配的用户信息

        return [
            'code' => 1,
            'data' => [
                $kfList[$sid][$flag]['uid'],
                $kfList[$sid][$flag]['name'],
                $kfList[$sid][$flag]['client_id'],
                $user,
                $kfList,
                $userList,
                $sid
            ]
        ];
    }
}
