var serviceInfo = {
    id: 'KF' + uid,
    username: uname,
    avatar: avatar,
    group: group
};
//创建socket实例
var socket = new WebSocket('ws://' + socket_server);

socket.onopen = function (res) {
    //登录
    var login_data = '{"type":"init", "uid":"' + serviceInfo.id + '", "name" : "' + serviceInfo.username + '", "avatar" : "'
        + serviceInfo.avatar + '", "group": ' + serviceInfo.group + '}';
    socket.send(login_data);
};

//监听消息
socket.onmessage = function (res) {
    var data = eval("(" + res.data + ")");
    switch (data['message_type']) {
        // 服务端ping客户端
        case 'ping':
            socket.send('{"type":"ping"}');
            break;
        // 添加用户
        case 'connect':
            addUser(data.data.user_info);
            break;
        // 移除访客到主面板
        // case 'delUser':
        //     delUser(data.data);
        //     break;
        // 监测聊天数据
        case 'chatMessage':
            showUserMessage(data.data, data.data.content);
            break;
    }
}
// 监听失败
socket.onerror = function (err) {
    alert('连接失败,请联系管理员');
};
$(function () {
    //获取服务用户列表
    if (userList.length > 0) {
        $.each(userList, function (k, v) {
            addUser(v);
        });
        var id = $(".unselect").find('li').eq(0).data('id');
        var name = $(".unselect").find('li').eq(0).data('name');
        var avatar = $(".unselect").find('li').eq(0).data('avatar');
        var ip = $(".unselect").find('li').eq(0).data('ip');
        $("#active-user").attr('data-id', id).attr('data-name', name).attr('data-avatar', avatar).attr('data-ip', ip);
        getChatLog(id, 1);
    }
    // 发送消息
    $("#send").click(function () {
        sendMessage();
    });
})

//获取聊天记录
function getChatLog(uid, page, flag) {
    $.getJSON(chatLogUrl, { uid: uid, page: page }, function (res) {
        var _html = '';
        console.log('res: ', res);
        var len = res.message.message.length;
        var data = res.message.message;
        for (var i = 0; i < len; i++) {
            if(data[i].from_id == serviceInfo.id){
                _html += '<li class="chat-mine">';
            }else{
                _html += '<li>'
            }
            _html += '<div class="chat-user">';
            _html += '<img src="' + data[i].from_avatar + '">';
            if(data[i].from_id == serviceInfo.id){
                _html += '<cite><i>' + data[i].time_line + '</i>' + data[i].from_name + '</cite>';
            }else{
                _html += '<cite>' + data[i].from_name + '<i>' + data[i].time_line + '</i></cite>';
            }
            _html += '</div><div class="chat-text">' + data[i].content + '</div>';
            _html += '</li>';
        }

        setTimeout(function(){
            $('#u-' + uid).html(_html);
        },100);

    })
}

// 消息发送工厂
function msgFactory(content, type, uinfo) {
    var _html = '';
    if ('mine' == type) {
        _html += '<li class="chat-mine">';
    } else {
        _html += '<li>';
    }
    _html += '<div class="chat-user">';
    _html += '<img src="' + uinfo.avatar + '">';
    if ('mine' == type) {
        _html += '<cite><i>' + getDate() + '</i>' + uinfo.username + '</cite>';
    } else {
        _html += '<cite>' + uinfo.name + '<i>' + getDate() + '</i></cite>';
    }
    _html += '</div><div class="chat-text">' + content + '</div>';
    _html += '</li>';

    return _html;
}
// 获取日期
function getDate() {
    var d = new Date(new Date());

    return d.getFullYear() + '-' + digit(d.getMonth() + 1) + '-' + digit(d.getDate())
        + ' ' + digit(d.getHours()) + ':' + digit(d.getMinutes()) + ':' + digit(d.getSeconds());
}
//补齐数位
var digit = function (num) {
    return num < 10 ? '0' + (num | 0) : num;
};
//添加用户列表
function addUser(data) {
    var _html = '<li class="side-nav-item" data-id="' + data.id + '" id="f-' + data.id +
        '" data-name="' + data.name + '" data-avatar="' + data.avatar + '" data-ip="' + data.ip + '">';
    _html += '<img src="' + data.avatar + '">';
    _html += '<span class="user-name">' + data.name + '</span>';
    _html += '</li>';
    // 添加左侧列表
    $("#user_list").append(_html);

    // 如果没有选中人，选中第一个
    var hasActive = 0;
    $('#user_list li').each(function () {
        if ($(this).hasClass('active')) {
            hasActive = 1;
        }
    });

    var _html2 = '';
    _html2 += '<ul id="u-' + data.id + '">';
    _html2 += '</ul>';
    // 添加主聊天面板
    $('.chat-box').append(_html2);

    if (0 == hasActive) {
        $("#user_list").find('li').eq(0).addClass('active');
        $("#u-" + data.id).show();

        var id = $(".unselect").find('li').eq(0).data('id');
        var name = $(".unselect").find('li').eq(0).data('name');
        var ip = $(".unselect").find('li').eq(0).data('ip');
        var avatar = $(".unselect").find('li').eq(0).data('avatar');

         // 设置当前会话用户
         $("#active-user").attr('data-id', id).attr('data-name', name).attr('data-avatar', avatar).attr('data-ip', ip);
    }
    getChatLog(data.id, 1);
    checkUser();
}

// 操作新连接用户的 dom操作
function checkUser() {
    $(".unselect").find('li').unbind("click"); // 防止事件叠加
    // 切换用户
    $(".unselect").find('li').bind('click', function () {
        changeUserTab($(this));
        var uid = $(this).data('id');
        var avatar = $(this).data('avatar');
        var name = $(this).data('name');
        var ip = $(this).data('ip');
        // 展示相应的对话信息
        $('.chat-box ul').each(function () {
            if ('u-' + uid == $(this).attr('id')) {
                $(this).addClass('show-chat-detail').siblings().removeClass('show-chat-detail').attr('style', '');
                return false;
            }
        });

        // 去除消息提示
        // $(this).find('span').eq(1).removeClass('layui-badge').text('');

        // 设置当前会话的用户
        $("#active-user").attr('data-id', uid).attr('data-name', name).attr('data-avatar', avatar).attr('data-ip', ip);

        // 右侧展示详情
        // $("#f-user").val(name);
        // $("#f-ip").val(ip);
        // $.getJSON('/service/index/getCity', { ip: ip }, function (res) {
        //     $("#f-area").val(res.data);
        // });

        // getChatLog(uid, 1);

        wordBottom();
    });
}
// 切换在线用户
function changeUserTab(obj) {
    obj.addClass('active').siblings().removeClass('active');
    wordBottom();
}
// 滚动条自动定位到最底端
function wordBottom() {
    var box = $(".chat-box");
    box.scrollTop(box[0].scrollHeight);
}

// 发送消息
function sendMessage(sendMsg) {
    var msg = (typeof (sendMsg) == 'undefined') ? $(".msg-area").val() : sendMsg;
    if ('' == msg) {
        alert('请输入回复内容');
    }

    var word = msgFactory(msg, 'mine', serviceInfo);
    var uid = $("#active-user").attr('data-id');
    var uname = $("#active-user").attr('data-name');

    socket.send(JSON.stringify({
        type: 'chatMessage',
        data: {
            to_id: uid, to_name: uname, content: msg, from_name: serviceInfo.username,
            from_id: serviceInfo.id, from_avatar: serviceInfo.avatar
        }
    }));

    $("#u-" + uid).append(word);
    $(".msg-area").val('');
    // 滚动条自动定位到最底端
    wordBottom();
}

// 展示客服发送来的消息
function showUserMessage(uinfo, content) {
    if ($('#f-' + uinfo.id).length == 0) {
        addUser(uinfo);
    }

    // // 未读条数计数
    // if (!$('#f-' + uinfo.id).hasClass('active')) {
    //     var num = $('#f-' + uinfo.id).find('span:eq(1)').text();
    //     if (num == '') num = 0;
    //     num = parseInt(num) + 1;
    //     $('#f-' + uinfo.id).find('span:eq(1)').removeClass('layui-badge').addClass('layui-badge').text(num);
    // }

    var word = msgFactory(content, 'user', uinfo);
    setTimeout(function () {
        $("#u-" + uinfo.id).append(word);
        // 滚动条自动定位到最底端
        wordBottom();

    }, 200);
}