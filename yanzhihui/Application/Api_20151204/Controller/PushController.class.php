<?php
namespace Api\Controller;

use JPush\Model as M;
use JPush\JPushClient;

class PushController extends BaseController {
    /**
     * 推送 push_message
     * @param null $push_iOS
     * @param null $push_Android
     */
    public function push_message_registration($ids= array(), $notification = array()) {
        if($ids && $notification) {
            vendor('jpush.autoload');
            $app_key = C('API_PUSH_JPUSH.key');
            $master_secret = C('API_PUSH_JPUSH.secret');
            $client = new JPushClient($app_key, $master_secret);

            $client->push()
                ->setPlatform(M\all)
                ->setAudience(M\registration_id($ids))
                ->setNotification(M\notification($notification['title'], M\android($notification['title'], $notification['title'], null, $notification['extras']), M\ios($notification['title'], 'default', 1)))
//                ->setMessage(M\message($notification['title'], $notification['title'], $notification['title'], $notification['extras']))
//                ->printJSON()
->setOptions(M\options(null, null, null, true))
                ->send();
        }
    }
}
