<?php
namespace Api\Controller;

class TopicController extends BaseController {
    /**
     * 话题列表 index
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
     * 关注话题列表 index_attention
     */
    public function index_attention($user_id = NULL) {
        if (IS_GET && $user_id) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index_attention();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    /**
     * 排行话题列表 index_rank
     */
    public function index_rank() {
        if (IS_GET) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index_rank();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    /**
     * 话题详情 article
     */
    public function article() {
        if (IS_GET) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $jsonData = D($name)->do_article();
            if ($jsonData['data']) {
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
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
     * 发布话题 add
     */
    public function add() {
        if (IS_POST) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $model = D($name);
            $result = $model->do_add();
            /* 返回信息 */
            if ($result) {
                /* 移动图片 */
                $upfile = I('post.upfile');
                $this->_after_do_uploads($name, $upfile);
                $jsonData = array('topic_id' => $result);
                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /*删除话题*/
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
}