<?php
namespace Api\Controller;

class UserAttentionController extends BaseController {
    /**
     * 关注 add
     */
    public function add($user_id = NULL, $to_user_id = NULL) {
        if (IS_POST && $user_id && $to_user_id) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_add($user_id, $to_user_id);
            /* 返回信息 */
            if ($result) {
                /* 获取关注状态 */
                $jsonData = array('attention_relation' => get_user_attention($user_id, $to_user_id));
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 取消关注 delete
     */
    public function delete($user_id = NULL, $to_user_id = NULL) {
        if (IS_POST && $user_id && $to_user_id) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_delete($user_id, $to_user_id);
            /* 返回信息 */
            if ($result) {
                /* 获取关注状态 */
                $jsonData = array('attention_relation' => strval(0));
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }
}