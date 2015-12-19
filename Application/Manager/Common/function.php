<?php
/**
 * 检测当前用户是否为超级管理员 is_administrator
 * @param null $uid
 * @return bool|int|mixed|null
 */
function is_administrator($uid = NULL) {
    $uid = is_null($uid) ? is_login() : $uid;
    return $uid && (intval($uid) === C('MANAGER_ADMINISTRATOR'));
}

/**
 * 验证权限 view_auth_check
 * @param null $rule
 * @return bool
 */
function view_auth_check($rule = NULL) {
    /* 管理员允许访问任何页面 */
    if (IS_ROOT) {
        return true;
    }
    /* 验证权限 */
    $Auth = new \Manager\ORG\Auth();
    $rule = substr(strtolower($rule), 1);
    if ($Auth->check($rule, UID)) {
        return true;
    }
    return false;
}

/**
 * 编辑器上传文件 upload_file_editor
 * @param $path
 */
function upload_file_editor($path = NULL) {
    /* 定义参数 */
    $dir_name = ucfirst(I('get.dir'));
    if (!$path) {
        $path = CONTROLLER_NAME;
    }
    $savePath = './Editor/' . $dir_name . '/' . $path . '/'; //上传地址
    $saveUrl = '/Uploads/Editor/' . $dir_name . '/' . $path . '/';   //读取地址

    switch ($dir_name) {
        case 'Flash':
            $options = array(
                'maxSize'  => 1024 * 1024 * 2, //上传的文件大小限制 (0-不做限制)
                'exts'     => array('swf', 'flv'), //允许上传的文件后缀
                'savePath' => $savePath        //保存路径
            );
            break;
        case 'Media':
            $options = array(
                'maxSize'  => 1024 * 1024 * 5, //上传的文件大小限制 (0-不做限制)
                'exts'     => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'), //允许上传的文件后缀
                'savePath' => $savePath        //保存路径
            );
            break;
        case 'File':
            $options = array(
                'maxSize'  => 1024 * 1024 * 5, //上传的文件大小限制 (0-不做限制)
                'exts'     => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'), //允许上传的文件后缀
                'savePath' => $savePath        //保存路径
            );
            break;
        default:
            $options = array(
                'maxSize'  => 1024 * 1024 * 0.5, //上传的文件大小限制 (0-不做限制)
                'exts'     => array('jpg', 'jpeg', 'gif', 'png'), //允许上传的文件后缀
                'savePath' => $savePath        //保存路径
            );
    }
    /* 上传文件 */
    $result = upload_file($options);
    if ($result['result'] == 1) { //上传成功
        $return['error'] = 0;
        $return['url'] = $saveUrl . $result['msg'];
    } else {
        $return['error'] = 1;
        $return['message'] = $result['msg'];
    }
    return $return;
}