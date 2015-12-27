<?php
namespace Api\Controller;

class TopicLikeController extends BaseController {
    /**
     * 点赞列表 index
     */
    public function index() {
        if (IS_GET) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    /**
     * 话题点赞 add
     */
    public function add($user_id = NULL, $topic_id = NULL, $IM_user_id = NULL, $IM_upload = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_add();
            /* 返回信息 */
            if ($result) {
                $push_user = D('User')->find_by_topic($topic_id);
                if($push_user['get_gold_notify']){
                    if(!empty($push_user['push_id'])){
                        $push_id = array();
                        $push_id[0] = $push_user['push_id'];
                        $notification = array('title'  => '你收到一个颜币','extras' => array());
                        R('Api/Push/push_message_registration', array($push_id, $notification));
                    }
                }
                if ($IM_upload) {
                    import('Api.ORG.EasemobIMSDK');
                    $rest = new \Hxcall();
                    $sender = C('EASEMOB.EASEMOB_PREFIX') . 'topic_like_add';
                    $receiver = C('EASEMOB.EASEMOB_PREFIX') . $IM_user_id;
                    $msg = '送你颜币';
                    $ext = array(
                        'type'     => 3,
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
}