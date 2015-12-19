<?php
namespace Account\Controller;

class IndexController extends BaseController {
    public function index() {
        $this->display();
    }

    public function home() {

        R('Api/Api/baidu_push_one', array($user_id = 1));


        exit;

        $this->display();
    }
}