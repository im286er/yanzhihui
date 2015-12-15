<?php
namespace Api\Controller;

class LevelController extends BaseController {
    
    public function index() {
        if (IS_GET) {
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }
}