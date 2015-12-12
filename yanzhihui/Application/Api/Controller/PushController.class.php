<?php
namespace Api\Controller;

use JPush\JPushClient;
use JPush\Model as M;

class PushController extends BaseController {
    /**
     * 推送 push_message
     */
    public function push_message_registration($ids = array(), $notification = array()) {
        if ($ids && $notification) {
            vendor('jpush.autoload');
            $app_key = C('API_PUSH_JPUSH.key');
            $master_secret = C('API_PUSH_JPUSH.secret');
            $client = new JPushClient($app_key, $master_secret);

            $client->push()
                ->setPlatform(M\all)
                ->setAudience(M\registration_id($ids))
                ->setNotification(M\notification($notification['title'], M\android($notification['title'], $notification['title'], NULL, $notification['extras']), M\ios($notification['title'], 'default', 1)))
                ->setOptions(M\options(NULL, NULL, NULL, true))
//                ->setMessage(M\message($notification['title'], $notification['title'], $notification['title'], $notification['extras']))
//                ->printJSON()
                ->send();
        }
    }
}