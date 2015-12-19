<?php
/**
 * 各个模块公用工具类
 */

/**
 * 判断是否登录 is_login
 * @return int|mixed
 */
function is_login() {
    $userId = session('user_id');
    if (empty($userId)) {
        session(NULL);
        return false;
    } else {
        return $userId;
    }
}

/**
 * 截取字数 subtext
 * @param $text
 * @param $length
 * @return string
 */
function subtext($text, $length) {
    if (mb_strlen($text, 'utf8') > $length) {
        return mb_substr($text, 0, $length, 'utf8') . '...';
    }
    return $text;
}

/**
 * 是否正整数 is_positive_int
 * @param $data
 * @return bool
 */
function is_positive_int($data){
    if(ceil($data) == $data && $data > 0){
        return true;
    }
    return false;
}

/**
 * 读取文件目录列表 get_dir
 * @param $dir
 * @return array|string
 */
function get_dir($dir) {
    $file = scandir($dir);
    $dirArray = '';
    foreach ($file as $name) {
        if ($name != '.' && $name != '..' && !strpos($name, '.') && substr($name, '0', '1') !== '.') {
            $dirArray[] = $name;
        }
    }
    return $dirArray;
}

/**
 * 读取文件夹下文件 get_file
 * @param $dir
 * @return array|string
 */
function get_file($dir) {
    $file = scandir($dir);
    $fileArray = '';
    foreach ($file as $name) {
        if ($name != '.' && $name != '..' && strpos($name, '.')) {
            $fileArray[] = $name;
        }
    }
    return $fileArray;
}

/**
 * 创建文件夹 mkdirss
 * @param $dirs
 * @param int $mode
 * @return bool
 */
function mkdirss($dirs, $mode = 0777) {
    if (!is_dir($dirs)) {
        mkdirss(dirname($dirs), $mode);
        return @mkdir($dirs, $mode);
    }
    return true;
}

/**
 * 把整个文件读入一个字符串中 read_file
 * @param $l1
 * @return bool|string
 */
function read_file($l1) {
    return @file_get_contents($l1);
}

/**
 * 把一个字符串写入文件中 write_file
 * @param $l1
 * @param $l2
 * @return int
 */
function write_file($l1, $l2) {
    $dir = dirname($l1);
    if (!is_dir($dir)) {
        mkdirss($dir);
    }
    return @file_put_contents($l1, $l2);
}

/**
 * 删除文件夹 del_dir_and_file
 * @param $dirName
 * @return bool
 */
function del_dir_and_file($dirName) {
    if ($handle = opendir($dirName)) {
        while (false !== ($item = readdir($handle))) {
            $newDirName = $dirName . '/' . $item;
            if ($item != "." && $item != "..") {
                if (is_dir($newDirName)) {
                    del_dir_and_file($newDirName);
                } else {
                    unlink($newDirName);
                }
            }
        }
        closedir($handle);
        if (rmdir($dirName)) {
            return true;
        }
        return false;
    }
}

/**
 * 删除文件 del_file
 * @param $fileName
 * @return bool
 */
function del_file($fileName) {
    if (file_exists($fileName)) {
        if (unlink($fileName)) {
            return true;
        }
        return false;
    }
}

/**
 * 删除静态 do_delete_html
 * @param null $path
 * @param null $file
 */
function do_delete_html($path = NULL, $file = NULL) {
    if ($path && $file) {
        $file = explode(',', $file);
        foreach ($file as $v) {
            $fileName = HTML_PATH . $path . '/' . $v . '.html';
            del_file($fileName); //删除静态
        }
    }
}

/**
 * 返回json json_return
 * @param $data
 * @return string
 */
function json_return($data) {
    header('Content-Type:application/json; charset=utf-8');
    return json_encode($data);
}

/**
 * logs 文件操作 logs_write_file
 * @param null $path
 * @param null $data
 */
function logs_write_file($path = NULL, $logs = NULL) {
    if ($path && $logs) {
        mkdirss($path);
        $filename = $path . '/' . date('Ymd') . '.log';
        $logs = $logs . "\n\n";
        error_log($logs, 3, $filename);
    }
}

/**
 * 数据操作日志 logs_action_operate
 * @param null $remark
 * @param null $logs_data
 * @param null $logs_options
 */
function logs_action_operate($remark = NULL, $logs_data = NULL, $logs_options = NULL) {
    if ($remark) {
        $uid = is_login();
        $data[] = '操作类型:' . $remark;
        $data[] = '操作用户:' . $uid;
        $data[] = '操作时间:' . date('Y-m-d H:i:s');
        $data[] = '操作IP:' . get_client_ip();
        $data[] = 'URL来源:' . $_SERVER['HTTP_REFERER'];
        if ($logs_options) {
            $data[] = '操作模型:' . $logs_options['model'];
            $data[] = '操作数据表:' . $logs_options['table'];
        }
        if ($logs_data) {
            $data[] = '操作内容:' . json_encode($logs_data);
        }
        /* 记录文件 */
        $path = './Logs/' . MODULE_NAME . '/ActionOperate/' . $uid;
        $logs = implode("\n", $data);
        logs_write_file($path, $logs);
    }
}

/**
 * 捕获异常日志 logs_system_error
 * @param null $remark
 */
function logs_system_error($remark = NULL) {
    if ($remark) {
        $uid = is_login();
        $data[] = '操作用户:' . $uid;
        $data[] = '操作时间:' . date('Y-m-d H:i:s');
        $data[] = '操作IP:' . get_client_ip();
        $data[] = 'URL来源:' . $_SERVER['HTTP_REFERER'];
        $data[] = '捕获异常:' . $remark;
        /* 记录文件 */
        $path = './Logs/' . MODULE_NAME . '/SystemError';
        $logs = implode("\n", $data);
        logs_write_file($path, $logs);
    }
}

/**
 * 文件操作 logs_upload_file
 * @param null $action
 * @param null $remark
 */
function logs_upload_file($action = NULL, $remark = NULL) {
    if ($action && $remark) {
        $uid = is_login();
        $data[] = '操作行为:' . $action;
        $data[] = '操作用户:' . $uid;
        $data[] = '操作时间:' . date('Y-m-d H:i:s');
        $data[] = '操作IP:' . get_client_ip();
        $data[] = 'URL来源:' . $_SERVER['HTTP_REFERER'];
        $data[] = '操作信息:' . $remark;
        /* 记录文件 */
        $path = './Logs/' . MODULE_NAME . '/UploadFile/' . $uid;
        $logs = implode("\n", $data);
        logs_write_file($path, $logs);
    }
}

/**
 * 上传文件 upload_file
 * @param array $options
 * @return array
 */
function upload_file($options = array()) {
    $upload = new \Think\Upload();
    foreach (C('FILE_UPLOAD') as $k => $v) {
        $upload->$k = $v;
    }
    if ($options && is_array($options)) {
        foreach ($options as $k => $v) {
            $upload->$k = $v;
        }
    }
    /* 上传文件 */
    $info = $upload->upload();
    if (!$info) {
        $msg = $upload->getError();
        /* 记录上传日志 */
        logs_upload_file('上传文件失败', $msg);
        $result = array('result' => 0, 'msg' => $msg);
    } else {
        $savenameArr = array();
        foreach ($info as $k => $v) {
            $savenameArr[] = $v['savename'];
            /* 记录上传日志 */
            logs_upload_file('上传文件成功', json_encode($v));
        }
        $savename = implode(',', $savenameArr);
        $result = array('result' => 1, 'msg' => $savename);
    }
    return $result;
}

/**
 * 移动文件 move_upload_file
 * @param $path
 * @param null $upfile
 * @return bool
 */
function move_upload_file($path, $upfile = NULL) {
    if (IS_POST && $upfile) {
        /* 新文件目录 */
        $path = './Uploads/' . $path . '/' . date('Ymd') . '/';
        mkdirss($path);
        /* 移动图片 */
        $upfileArr = explode(',', $upfile);
        foreach ($upfileArr as $v) {
            $file = './Uploads/Temp/' . $v; //临时文件
            if (is_file($file)) {
                $newFile = $path . $v; //新文件
                if (rename($file, $newFile)) {
                    /* 记录文件移动日志 */
                    logs_upload_file('文件移动成功', $newFile);
                } else {
                    /* 记录文件移动日志 */
                    logs_upload_file('文件移动失败', $file);
                }
            }
        }
    }
}

/**
 * 获取上传地址 get_upfile
 * @param $data
 * @return string
 */
function get_upfile($data) {
    $upfileArr = array();
    if ($data) {
        $upfile_list = array_filter(explode(',', $data));
        if ($upfile_list) {
            foreach ($upfile_list as $v) {
                if (!strpos($v, '/')) {
                    $upfileArr[] = date('Ymd') . '/' . $v;
                } else {
                    $upfileArr[] = $v;
                }
            }
        }
    }
    return implode(',', $upfileArr);
}

/**
 * 10进制转为36进制
 * @param $n
 * @return string
 */
function dec36($n) {
    $base = 34;
    $index = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    $ret = '';
    for($t = floor(log10($n) / log10($base)); $t >= 0; $t --) {
        $a = floor($n / pow($base, $t));
        $ret .= substr($index, $a, 1);
        $n -= $a * pow($base, $t);
    }
    return $ret;
}

/**
 * 36进制转为10进制
 * @param $s
 * @return bool|int
 */
function dec10($s) {
    $base = 34;
    $index = '123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
    $ret = 0;
    $len = strlen($s) - 1;
    for ($t = 0; $t <= $len; $t++) {
        $ret += strpos($index, substr($s, $t, 1)) * pow($base, $len - $t);
    }
    return $ret;
}

/**
 * 验证 url
 * @param $data
 * @return bool
 */
function is_url($data) {
    $isTure = preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\"])*$/", $data);
    if ($isTure) {
        return true;
    }
    return false;
}

/**
 * 验证表单错误提示
 * @param null $result
 * @return array
 */
function validate_error($result = NULL) {
    $errorMsg = array();
    if ($result) {
        foreach ($result as $k => $v) {
            $errorMsg[] = array('name' => $k, 'msg' => $v);
        }
    }
    return $errorMsg;
}

/**
 * 获取去向 user_id, shop_id 等资料
 */
function get_shop_user_went($user_id = NULL, $shop_id = NULL){
    $data_shop_user_went = null;
    if($user_id && $shop_id){
        $field_shop_user_went = 'shop_user_went.shop_id,shop_user_went.user_id,shop_user_went.content,shop_user_went.create_time,
                                 shop.title as shop_title,shop.upfile,
                                 user.nick_name,user.upfile_head';
        $where_shop_user_went['user_id'] = array('EQ', $user_id);
        $where_shop_user_went['shop_id'] = array('EQ', $shop_id);
        $data_shop_user_went = M('ShopUserWent')
            ->alias('shop_user_went')
            ->field($field_shop_user_went)
            ->where($where_shop_user_went)
            ->join('__USER__ user on shop_user_went.user_id = user.id')
            ->join('__SHOP__ shop on shop_user_went.shop_id = shop.id')
->order('shop_user_went.create_time desc')
            ->find();
if($data_shop_user_went){
            if ($data_shop_user_went['upfile']) {
                $data_shop_user_went['upfile'] = C('APP_URL') . '/Uploads/Images/Shop/' . $data_shop_user_went['upfile'];
            }
            if ($data_shop_user_went['upfile_head'] && !strstr($data_shop_user_went['upfile_head'], 'http://')) {
                $data_shop_user_went['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $data_shop_user_went['upfile_head'];
            }
        }
    }
    return $data_shop_user_went;
}
