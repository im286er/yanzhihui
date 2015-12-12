<?php
namespace Api\Controller;

use Think\Controller;

class BaseController extends Controller {
    public function _initialize() {
        /* 检测需要验证 user_id,auth_token 的节点*/
        $check = str_replace('_', '', strtolower(CONTROLLER_NAME)) . '/' . ACTION_NAME;
        if (in_array_case($check, C('AUTH_TOKEN_CHECK'))) {
            token_check();
        }
    }

    /**
     * 404错误页
     */
    public function _empty() {
        R('Empty/index');
    }

    /**
     * return_post 回调 post 数据
     * @param $model
     */
    public function return_post($model) {
        $errorMsg = $model->getError();
        if ($errorMsg) {
            $this->ajaxReturn(array('RESPONSE_STATUS' => 403, 'Tips' => $errorMsg));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 推送
     */
    public function push_one($push_id, $data_shop_user_went = array()) {
        $title = '你关注的人有新的去向';
        $notification = array(
            'title'       => $title,
            'extras'     => $data_shop_user_went
        );
        R('Api/Push/push_message_registration', array($push_id, $notification));
    }

    /**
     * 回调上传移动图片函数 _after_do_uploads
     * @param null $name
     * @param null $upfile
     */
    protected function _after_do_uploads($name = NULL, $upfile = NULL) {
        $path = 'Images/' . $name;
        move_upload_file($path, $upfile);
    }
}