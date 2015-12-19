<?php
namespace Api\Controller;

class TopicCommentController extends BaseController {
    /**
     * 话题评论列表 index
     */
    public function index() {
        if (IS_GET) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    public function test(){
        $push = D('User')->find_push_status(1,1);
        echo $push;
    }

    /**
     * 发布话题评论 add
     */
    public function add($user_id = NULL, $topic_id = NULL, $IM_user_id = NULL, $IM_upload = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_add();
            /* 返回信息 */
            if ($result) {
                /* 查询被推送用户的推送状态 */
                $push_status = D('User')->find_push_status($IM_user_id,1);
                /* 成功并且IM允许，推送IM */
                if ($IM_upload && ($push_status == 1)) {
                    import('Api.ORG.EasemobIMSDK');
                    $rest = new \Hxcall();
                    $sender = C('EASEMOB.EASEMOB_PREFIX') . 'topic_comment_add';
                    $receiver = C('EASEMOB.EASEMOB_PREFIX') . $IM_user_id;
                    $msg = '评论: ' . I('post.content');
                    $ext = array(
                        'type'     => 2,
                        'id'       => $topic_id,
                        'username' => C('EASEMOB.EASEMOB_PREFIX') . $user_id,
                        'upload'   => $IM_upload,
                        'remarks'  => ''
                    );
                    $rest->hx_send($sender, $receiver, $msg, $ext);
                }
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 删除评论 delete
     */
    public function delete() {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_delete();
            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /*
     * 删除他人的评论
     */
    public function deletefans() {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);

            $result = $model->do_deletefans();
            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }
}