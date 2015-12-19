<?php
namespace Home\Controller;

use Think\Controller;

class EmptyController extends Controller {
    public function index() {
        header('HTTP/1.0 404 Not Found');
        $this->display('Manager@Public:404');
        exit;
    }

    public function _empty() {
        R('Empty/index');
    }
}