<?php
namespace Manager\Controller;

use Think\Controller;

class PublicController extends Controller {
    /*
     * 用户登录页面
     */
    public function login() {
        if (is_login()) {
            $this->redirect(__MODULE__);
        } else {
            $this->display();
        }
    }

    /*
     * 验证码
     */
    public function verify() {
        $config = array(
            'imageW'   => '80',
            'imageH'   => '32',
            'fontSize' => '12',
            'bg'       => array(51, 51, 51),
            'length'   => '4',
            'fontttf'  => 'Helvetica.ttf',
            'useCurve' => false,
            'useNoise' => false,
        );
        $verify = new \Manager\ORG\Verify($config);
        $verify->entry();
    }

    /**
     * 登录检测
     */
    public function loginCheck() {
        $result = array('msg' => L('YZ_unknown_error'));
        if (IS_POST && IS_AJAX) {
            $model = D('UcenterMember');
            $result = $model->login();
        }
        $this->ajaxReturn($result);
    }

    /**
     * 修改密码
     */
    public function password() {
        $this->display();
    }

    /**
     * 保存修改密码 updatePassword
     */
    public function updatePassword() {
        $model = D('Member');
        $result = $model->do_update_password();

        /* 返回信息 */
        if ($result) {
            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1, 'href' => U('password')));
        } else {
            $result = $model->getError();
            if (is_array($result) && count($result)) {
                /* 验证错误 */
                $errorMsg = validate_error($result);
                $this->ajaxReturn(array('formError' => $errorMsg, 'result' => -1));
            } else {
                /* 数据库操作错误 */
                $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
            }
        }
    }

    /*
     * 注销用户
     */
    public function logout() {
        logs_action_operate('退出登录');
        session(NULL);
        $this->redirect('login');
    }

    /**
     * 404错误页
     */
    public function _empty() {
        R('Empty/index');
    }
}