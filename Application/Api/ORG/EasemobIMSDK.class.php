<?php

class Hxcall {
    private $client_id;
    private $client_secret;
    private $url;

    function __construct() {
        $this->setAccount();

        $url = $this->url . "/token";
        $data = array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret
        );
        $rs = json_decode($this->curl($url, $data), true);
        $this->token = $rs['access_token'];
    }

    /*
     * 获取APP管理员Token
     */

    /**
     * 设置主帐号
     */
    function setAccount() {
        $this->client_id = C('EASEMOB.client_id');
        $this->client_secret = C('EASEMOB.client_secret');
        $this->url = 'https://a1.easemob.com/' . C('EASEMOB.AppKey');
    }

    /*
     * 注册IM用户(授权注册)
     */

    private function curl($url, $data, $header = false, $method = "POST") {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $ret = curl_exec($ch);
        return $ret;
    }

    /*
     * 给IM用户的添加好友
     */

    public function hx_register($username, $password, $nickname) {
        $url = $this->url . "/users";
        $data = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        );
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, $data, $header, "POST");
    }

    /*
     * 解除IM用户的好友关系
     */

    public function hx_contacts($owner_username, $friend_username) {
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "POST");
    }

    /*
     * 查看好友
     */

    public function hx_contacts_delete($owner_username, $friend_username) {
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "DELETE");
    }

    /* 发送文本消息 */

    public function hx_contacts_user($owner_username) {
        $url = $this->url . "/users/${owner_username}/contacts/users";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /* 查询离线消息数 获取一个IM用户的离线消息数 */

    public function hx_send($sender, $receiver, $msg, $ext = array()) {
        $url = $this->url . "/messages";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        $data = array(
            'target_type' => 'users', //users 给用户发消息, chatgroups 给群发消息
            // 注意这里需要用数组,数组长度建议不大于20, 即使只有一个用户,
            // 也要用数组 ['u1'], 给用户发送时数组元素是用户名,给群组发送时
            // 数组元素是groupid
            'target'      => array(
                '0' => $receiver
            ),
            'msg'         => array( //消息内容
                'type' => "txt",
                'msg'  => $msg
            ),
            'from'        => $sender, //表示这个消息是谁发出来的, 可以没有这个属性, 那么就会显示是admin, 如果有的话, 则会显示是这个用户发出的
            'ext'         => $ext //扩展属性, 由app自己定义.可以没有这个字段，但是如果有，值不能是“ext:null“这种形式，否则出错
        );
        return $this->curl($url, $data, $header, "POST");
    }

    /*
     * 获取IM用户[单个]
     */

    public function hx_msg_count($owner_username) {
        $url = $this->url . "/users/${owner_username}/offline_msg_count";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /*
     * 获取IM用户[批量]
     */

    public function hx_user_info($username) {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /*
     * 重置IM用户密码
     */

    public function hx_user_infos($limit) {
        $url = $this->url . "/users?${limit}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET");
    }

    /*
     * 删除IM用户[单个]
     */

    public function hx_user_update_password($username, $newpassword) {
        $url = $this->url . "/users/${username}/password";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        $data['newpassword'] = $newpassword;
        return $this->curl($url, $data, $header, "PUT");
    }

    /*
     * 修改用户昵称
     */

    public function hx_user_delete($username) {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "DELETE");
    }

    /*
     * curl
     */

    public function hx_user_update_nickname($username, $nickname) {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization: Bearer ' . $this->token
        );
        $data['nickname'] = $nickname;
        return $this->curl($url, $data, $header, "PUT");
    }
}