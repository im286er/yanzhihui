<?php
namespace Api\Controller;

class TopicLogController extends BaseController {
    

    /**
     * 添加上榜记录
     */
    public function add() {
        $model = D(CONTROLLER_NAME);
        $model->do_add();
        $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
    }

    /**
     * 获取我的上榜记录
     */
    public function my_list(){
        if(IS_GET){
            $model = D(CONTROLLER_NAME);
            $jsonData = $model->do_list();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }


}