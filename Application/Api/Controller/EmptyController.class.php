<?php
namespace Api\Controller;

use Think\Controller;

class EmptyController extends Controller {
    public function index() {
        $this->ajaxReturn(array('RESPONSE_STATUS' => 404, 'Tips' => L('YZ_noData_exist')));
    }

    public function _empty() {
        R('Empty/index');
    }
}