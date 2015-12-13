<?php
namespace Api\Controller;

class UserController extends BaseController {



    /**
     * 获取我的去向
     */
    public function my_went() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_my_went();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }



    /**
     * 修改私信门槛
     */
    public function edit_chat_level() {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_edit_chat_level();
            /* 返回信息 */
            //var_dump($result);
            if ($result) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }


    /**
     * 修改通知开关
     */
    public function edit_notify() {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_edit_notify();
            /* 返回信息 */
            //var_dump($result);
            if ($result) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }


    /**
     * 获取验证码
     */
    public function sendsms($type = NULL, $telephone = NULL) {
        if (IS_POST && in_array($type, array('register', 'password_reset'))) {
            $model = D('Sendsms');
            if ($type == 'register') {
                $result = $model->do_register($telephone);
            } else {
                $result = $model->do_forget($telephone);
            }
            /* 返回信息 */
            if ($result) {
                /* 定义变量 */
                $captchaTime = 'captchaTime_' . $telephone . '_' . $type;
                $captchaCode = 'captchaCode_' . $telephone . '_' . $type;
                /* 存入缓存 */
                S($captchaTime, NOW_TIME); //验证时间
                S($captchaCode, $result, C('API_SMS.lost_time')); //验证码
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => '发送成功'));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 注册用户
     */
    public function register($telephone = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_register();
            /* 返回信息 */
            if ($result) {
                /* 消除验证码 */
                S('captchaTime_' . $telephone . '_register', NULL);
                S('captchaCode_' . $telephone . '_register', NULL);
                /* 自动转登录 */
                $resultLogin = $model->do_login();
                if ($resultLogin) {
                    $jsonData = get_user_info('', $telephone);
                    $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
                }
                $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 手机用户登录
     */
    public function login($telephone = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_login();
            /* 返回信息 */
            if ($result) {
                $jsonData = get_user_info('', $telephone);
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 第三方平台用户登录
     */
    public function login_other($user_type = 1, $open_id = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_login_other();
            /* 返回信息 */
            if ($result) {
                $jsonData = get_user_info('', '', array('user_type' => $user_type, 'open_id' => $open_id));
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 修改用户资料
     */
    public function edit_info($user_id = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $model = D($name);
            $result = $model->do_edit_info();
            /* 返回信息 */
            if ($result !== false) {
                /* 移动图片 */
                $upfile = I('post.upfile_head');
                $this->_after_do_uploads($name, $upfile);
                /* 返回用户信息 */
                $jsonData = get_user_info($user_id);
                if ($jsonData['data']) {
                    $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
                }
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 修改密码
     */
    public function edit_password() {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_edit_password();
            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    public function test(){
        $User = M("User");
        $list = $User->select();
        var_dump($list);
    }





    /**
     * 重置密码
     */
    public function password_reset($telephone = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_password_reset();
            /* 返回信息 */
            if ($result !== false) {
                /* 消除验证码 */
                S('captchaTime_' . $telephone . '_password_reset', NULL);
                S('captchaCode_' . $telephone . '_password_reset', NULL);
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 用户信息
     */
    public function info() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_info();
            if ($jsonData['data']) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 认证头像
     */
    public function authentication_upfile_head($user_id = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $model = D($name);
            $result = $model->do_authentication_upfile_head();
            /* 返回信息 */
            if ($result) {
                /* 移动图片 */
                $upfile = I('post.upfile_head_auth');
                $this->_after_do_uploads($name, $upfile);
                /* 返回用户信息 */
                $jsonData = get_user_info($user_id);
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 退出登录
     */
    public function logout() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_logout();
            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 用户关注列表 attention
     */
    public function attention() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_attention_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 用户粉丝列表 fans
     */
    public function fans() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_fans_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 获取用户 IM 信息 im_info
     */
    public function im_info() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_im_info();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 搜索用户名 search_nickname
     */
    public function search_nickname($keywords_nickname = NULL) {
        if (IS_GET && $keywords_nickname) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_search_nickname($keywords_nickname);
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }
}