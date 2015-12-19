<?php
namespace Home\Controller;

class ShopApplyController extends BaseController {
    public function index() {
        $this->display();
    }

    public function add() {
        if(IS_POST && IS_AJAX){
            $model = D(CONTROLLER_NAME);
            $result = $model->do_add();
            if ($result) {
                $this->ajaxReturn(array('result' => 100, 'msg' => '非常感谢，小颜已经收到您的推荐啦，请静候审核后的好消息，坐等300颜币入账吧~'));
            } else {
                $errorMsg = $model->getError();
                if ($errorMsg) {
                    $this->ajaxReturn(array('result' => 403, 'msg' => $errorMsg));
                }
                $this->ajaxReturn(array('result' => 500, 'msg' => '非常抱歉，提交失败，请稍后再提交'));
            }
        }
    }
}