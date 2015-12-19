<?php
namespace Api\Controller;

class ShopController extends BaseController {
    /**
     * 商家列表 index
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
     * 商家详情 article
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
}