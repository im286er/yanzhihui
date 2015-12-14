<?php
/**
 * 双向加密字符串 encrypt
 * @param $data
 * @return string
 */
function encrypt($data) {
    $char = '';
    $str = '';
    $key = md5(C('APP_FLAG'));
    $x = 0;
    $data = strval($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= $key{$x};
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
    }
    return base64_encode($str);
}

/**
 * 双向解密字符串 decrypt
 * @param $data
 * @return string
 */
function decrypt($data) {
    $char = '';
    $str = '';
    $key = md5(C('APP_FLAG'));
    $x = 0;
    $data = base64_decode($data);
    $len = strlen($data);
    $l = strlen($key);
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return $str;
}

/**
 * 发送验证码 sendTemplateSMS
 * @param $to
 * @param $datas
 * @param $tempId
 * @return mixed|SimpleXMLElement|内容数据
 */
function sendTemplateSMS($to = NULL, $datas = NULL, $tempId = NULL) {
    $result = '';
    if ($to && $datas && $tempId) {
        $accountSid = C('API_SMS.accounts_id');     //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        $accountToken = C('API_SMS.account_token'); //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        $appId = C('API_SMS.app_id');               //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID, 在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
        $serverIP = C('API_SMS.server_ip');         //请求地址 沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com,生产环境（用户应用上线使用）：app.cloopen.com
        $serverPort = C('API_SMS.server_port');     //请求端口，生产环境和沙盒环境一致
        $softVersion = C('API_SMS.soft_version');   //REST版本号，在官网文档REST介绍中获得。
        import('@.ORG.CCPRestSmsSDK');
        $rest = new \REST($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);
        /* 发送模板短信 */
        $result = $rest->sendTemplateSMS($to, $datas, $tempId);
    }
    return $result;
}

/**
 * 生成 get_auth_token
 * @param null $data
 * @return bool|string
 */
function get_auth_token() {
    return encrypt(NOW_TIME);
}

/**
 * 验证token token_check
 */
function token_check() {
    $auth_token = I('get.auth_token');
//    if (NOW_TIME - decrypt($auth_token) < 86400 * 7) {
    $where['id'] = array('EQ', I('get.user_id'));
    $where['auth_token'] = array('EQ', $auth_token);
    $where['status'] = array('EQ', 1);
    $where['display'] = array('EQ', 1);
    $count = M('User')->where($where)->count();
    if ($count == 1) {
        return true;
    }
//    }
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode(array('RESPONSE_STATUS' => 400, 'Tips' => L('YZ_authToken_error'))));
}

/**
 * 读取用户自己的信息
 */
function get_user_info($user_id = NULL, $telephone = NULL, $other = array()) {
    if ($user_id || $telephone || is_array($other)) {
        $field = 'id,telephone,nick_name,sex,upfile_head,description,province,city,upfile_head_auth,upfile_head_auth_type,IM_username,IM_password,like_count,like_now_count,like_consume_count,attention_count,fans_count,auth_token,comment_notify,get_gold_notify,trace_notify,letter_notify';
        if ($user_id) {
            $where['id'] = array('EQ', $user_id);
        } else if ($telephone) {
            $where['telephone'] = array('EQ', $telephone);
        } else {
            $where['user_type'] = array('EQ', $other['user_type']);
            $where['open_id'] = array('EQ', $other['open_id']);
        }
        $where['status'] = array('EQ', 1);
        $where['display'] = array('EQ', 1);
        $data = M('User')->field($field)->where($where)->find();
        /* 头像 */
        if ($data['upfile_head'] && !strstr($data['upfile_head'], 'http://')) {
            $data['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $data['upfile_head'];
        }
        /* 认证头像 */
        if ($data['upfile_head_auth']) {
            $data['upfile_head_auth'] = C('APP_URL') . '/Uploads/Images/User/' . $data['upfile_head_auth'];
        }
        /* 返回数据 */
        $return_data['data'] = arr_content_replace($data);
        return $return_data;
    }
}

/**
 * 读取用户之间关注状态
 */
function get_user_attention($user_id = NULL, $to_user_id = NULL) {
    $where['user_id'] = array('EQ', $user_id);
    $where['to_user_id'] = array('EQ', $to_user_id);
    $relation = M('UserAttention')->where($where)->getField('relation');
    return $relation;
}

/**
 * 读取用户之间屏蔽状态
 */
function get_user_blocked($user_id = NULL, $to_user_id = NULL) {
    $where['user_id'] = array('EQ', $user_id);
    $where['to_user_id'] = array('EQ', $to_user_id);
    $relation = M('UserBlocked')->where($where)->count();
    return $relation;
}

/**
 * 递归过滤数据
 */
function str_filter($str) {
    $str = htmlspecialchars_decode($str);
//    $str = urldecode($str);
    return $str;
}

function arr_content_replace($array) {
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            $array[$k] = arr_content_replace($array[$k]);
        }
    } else {
        $array = str_filter($array);
    }
    return $array;
}

/**
 * 查询城市
 */
function search_city($keyword = NULL) {
    $list = S('data_city');
    if (!$list) {
        $list = M('City')->getField('name', true);
        S('data_city', $list);
    }
    foreach ($list as $v) {
        if (strpos($v, $keyword) !== false)
            return $v;
    }
    return false;
}