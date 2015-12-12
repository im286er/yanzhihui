<?php
namespace Api\Controller;

class LiqihuaController extends BaseController {
    /**
     * 商家列表 index
     */
    public function index() {
        echo "liqihua - index()";
    }

    /**
     * 商家详情 article
     */
    public function article() {
        /* 定义变量 */
        $jsonData = array("a"=>"aa","b"=>"bb");
        $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonData));
    }
}