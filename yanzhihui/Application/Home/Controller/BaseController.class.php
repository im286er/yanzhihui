<?php
namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller {
    public function _initialize() {
    }

    /**
     * 404错误页
     */
    public function _empty() {
        R('Empty/index');
    }

    /**
     * 判断是否登录
     */
    protected function is_login_redirect() {
        if (!UID) {
            $this->redirect('/');
        }
    }
}