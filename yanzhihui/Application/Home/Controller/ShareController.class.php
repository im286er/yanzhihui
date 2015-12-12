<?php
namespace Home\Controller;

class ShareController extends BaseController {
    public function index() {
        $model = D('Topic');
        $vo = $model->do_data();
        if($vo['topic_id']){
            $this->assign('vo', $vo);
            $this->display();
        }else{
            R('Empty/index');
        }
    }
}