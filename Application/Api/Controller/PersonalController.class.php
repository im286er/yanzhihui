<?php
namespace Api\Controller;

class PersonalController extends BaseController {
    /**
     * 我的兑换 payorder
     */
    public function payorder() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D('PayOrder');
            $jsonData = $model->do_personal_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 查询被对方用户屏蔽
     */
    public function be_shielded_user() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D('UserBlocked');
            $jsonData = $model->do_be_shielded_user_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }
}