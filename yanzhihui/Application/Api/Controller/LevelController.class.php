<?php
namespace Api\Controller;

use JPush\JPushClient;
use JPush\Model as M;

class LevelController extends BaseController {
    
    public function index() {
        if (IS_GET) {
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    public function test_jpush(){
    	echo "33";
    	$push_id = array('0617fb1fbb9');
    	$notification = array(
            'title'  => 'rrrrrrrrrr',
            'extras' => array()
        );
    	R('Api/Push/push_message_registration', array($push_id, $notification));
    }

    public function test(){
        $model = D('User');
        //$result = $model->find_by_IM('b056d2da-9d76-11e5-9724-898879acdf4a');
        $result = $model->find_by_topic(15637);
        var_dump($result['comment_notify']);
        if($result['comment_notify']){
            echo 'oo';
        }
    }

}