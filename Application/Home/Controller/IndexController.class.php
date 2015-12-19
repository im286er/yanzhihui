<?php
namespace Home\Controller;

class IndexController extends BaseController {
    public function index() {
	/* 下载链接 */
        $vo['data_setting'] = S('data_setting');
        $this->assign('vo', $vo);
        $this->display();
    }
}
