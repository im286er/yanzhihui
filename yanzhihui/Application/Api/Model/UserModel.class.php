<?php
namespace Api\Model;

class UserModel extends CommonModel {
    /* 插入模型数据 操作状态 */
    const MODEL_REGISTER = 4; //注册用户
    const MODEL_LOGIN = 5; //手机用户登录
    const MODEL_LOGIN_OTHER = 6; //第三方平台用户登录
    const MODEL_EDIT_INFO = 7; //修改资料
    const MODEL_EDIT_PASSWORD = 8; //修改密码
    const MODEL_RESET_PASSWORD = 9; //重置密码
    const MODEL_RESET_AUTH_UPFILE_HEAD = 10; //认证头像

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 注册用户 */
        array('telephone', 'require', '{%YZ_telephone_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_REGISTER),
        array('telephone', 'validate_telephone_noExist', '{%YZ_telephone_exist}', self::MUST_VALIDATE, 'callback', self::MODEL_REGISTER),
        array('password', 'require', '{%YZ_password_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_REGISTER),
        array('client_system', array(1, 2), '{%YZ_clientSystem_error}', self::MUST_VALIDATE, 'in', self::MODEL_REGISTER),
        array('captcha', 'validate_captcha', '{%YZ_captcha_error}', self::MUST_VALIDATE, 'callback', self::MODEL_REGISTER),
        /* 手机用户登录 */
        array('telephone', 'require', '{%YZ_telephone_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_LOGIN),
        array('telephone', 'validate_telephone_exist', '{%YZ_telephone_noExist}', self::MUST_VALIDATE, 'callback', self::MODEL_LOGIN),
        array('password', 'require', '{%YZ_password_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_LOGIN),
        array('client_system', array(1, 2), '{%YZ_clientSystem_error}', self::MUST_VALIDATE, 'in', self::MODEL_LOGIN),
        /* 第三方平台用户登录 */
        array('access_token', 'require', '{%YZ_wxAccessToken_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_LOGIN_OTHER),
        array('open_id', 'require', '{%YZ_wxOpenId_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_LOGIN_OTHER),
        array('client_system', array(1, 2), '{%YZ_clientSystem_error}', self::MUST_VALIDATE, 'in', self::MODEL_LOGIN_OTHER),
        /* 修改资料 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_EDIT_INFO),
        array('nick_name', 'require', '{%YZ_nickName_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_INFO),
        array('sex', array(1, 2), '{%YZ_sex_error}', self::MUST_VALIDATE, 'in', self::MODEL_EDIT_INFO),
//        array('upfile_head', 'require', '{%YZ_upfile_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_INFO),
//        array('province', 'require', '{%YZ_province_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_INFO),
//        array('city', 'require', '{%YZ_city_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_INFO),
//        array('area', 'require', '{%YZ_area_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_INFO),
        /* 修改密码 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_EDIT_PASSWORD),
        array('old_password', 'require', '{%YZ_password_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_PASSWORD),
        array('password', 'require', '{%YZ_newPassword_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_EDIT_PASSWORD),
        array('password', 'validate_updatePassword', '{%YZ_oldpassword_different_password}', self::VALUE_VALIDATE, 'callback', self::MODEL_EDIT_PASSWORD),
        /* 重置密码 */
        array('telephone', 'require', '{%YZ_telephone_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_RESET_PASSWORD),
        array('telephone', 'validate_telephone_exist', '{%YZ_telephone_noExist}', self::MUST_VALIDATE, 'callback', self::MODEL_RESET_PASSWORD),
        array('password', 'require', '{%YZ_newPassword_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_RESET_PASSWORD),
        array('captcha', 'validate_captcha', '{%YZ_captcha_error}', self::MUST_VALIDATE, 'callback', self::MODEL_RESET_PASSWORD),
        /* 认证头像 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_RESET_AUTH_UPFILE_HEAD),
    );
    /* 模型自动完成 */

    /* 数据操作 */


    public function find_by_topic($topic_id){
        $where_topic_id['id'] = array('EQ', $topic_id);
        $topic = M('Topic')->field('user_id')->where($where_topic_id)->limit(1)->find();
        $where_user_id['id'] = array('EQ', $topic['user_id']);
        $result = $this->where($where_user_id)->find();
        return $result;
    }

    public function find_by_IM($IM_user_id){
        $where['IM_uuid'] = array('EQ', $IM_user_id);
        $result = $this->where($where)->find();
        return $result;
    }


    /**
     * 我的去向
     */
    public function do_my_went() {
        /* 定义变量 */
        $user_id = I('get.user_id');
        $where['user_id'] = array('EQ', $user_id);
        $field = 'went.shop_id as shop_id,went.content as content,s.title as title,s.upfile as img,went.create_time as went_time';
        $order = 'went.create_time desc';
        $list = M('ShopUserWent')
            ->alias('went')
            ->field($field)
            ->where($where) 
            ->join('LEFT JOIN xian_shop s on s.id = went.shop_id')
            ->order($order)
            ->select();
        $_list = array();
        if($list){
            foreach ($list as $k => $v) {
                if($v['img']){
                    $v['img'] = C('APP_URL') . '/Uploads/Images/Shop/' . $v['img'];
                }
                /*if($v['went_time']){
                    $v['went_time'] = date("Y-m-d H:i:s",$v['went_time']);
                }*/
                $_list[] = $v;
            }
        }
        /* 读取json */
        $_list = empty($_list) ? array() : $_list;
        $jsonInfo['list'] = arr_content_replace($_list);
        return $jsonInfo;
    }



    /**
     * 修改私信门槛 do_edit_chat_level
     * 参数：user_id：用户id
     *       value：颜币值
     */
    public function do_edit_chat_level() {
        /* 定义变量 */
        $data = array();
        $user_id = I('post.user_id');
        $level = I('post.level');
        $data['chat_level'] = $level;
        $arr = M('Level')->select();
        if($level == 0){
            $data['chat_value'] = 0;
        }if($level == 1){
            $data['chat_value'] = $arr[0]['level_a'];
        }else if($level == 2){
            $data['chat_value'] = $arr[0]['level_b'];
        }else if($level == 3){
            $data['chat_value'] = $arr[0]['level_c'];
        }
        $where['id'] = array('EQ', $user_id);
        $result = $this->where($where)->save($data);
        return $result;
    }



    /**
     * 修改通知开关 do_edit_notify
     * 参数：type：1->评论通知开关，2->收到颜币通知开关，3->关注的人的去向开关，4->私信通知
     *       value：0->关闭，1->打开
     */
    public function do_edit_notify() {
        /* 定义变量 */
        $data = array();
        $user_id = I('post.user_id');
        $_type = I('post.type');
        $type=intval($_type);
        $_value = I('post.value');
        $value=intval($_value);
        if($type == 1){
            $data['comment_notify'] = $value;
        }else if($type == 2){
            $data['get_gold_notify'] = $value;
        }else if($type == 3){
            $data['trace_notify'] = $value;
        }else if($type == 4){
            $data['letter_notify'] = $value;
        }
        $where['id'] = array('EQ', $user_id);
        $result = $this->where($where)->save($data);
        return $result;
    }

    


    /**
     * 查询用户推送状态 find_push_status:$IM_user_id->用户id，$type->1评论2得到颜币3去向4私信
     */
    public function find_push_status($IM_user_id,$type) {
        //$where['id'] = array('EQ', $IM_user_id);
        $where['IM_uuid'] = array('EQ', $IM_user_id);
        $field = '';
        if($type == 1){
            $field = 'comment_notify';
        }else if($type == 2){
            $field = 'get_gold_notify';
        }else if($type == 3){
            $field = 'trace_notify';
        }else if($type == 4){
            $field = 'letter_notify';
        }
        $result = $this->where($where)->field($field)->select();
        return $result[0][$field];
    }




    
    /**
     * 注册用户 do_register
     */
    public function do_register() {
        //if ($this->create('', self::MODEL_REGISTER)) {
            /* 本地注册用户 */
            $data['telephone'] = I('post.telephone');
            $data['password'] = MD5(I('post.password'));
            $data['client_system'] = I('post.client_system');
            $data['push_id'] = I('post.push_id');
            $data['create_time'] = NOW_TIME;
            $data['user_type'] = 0;
            $result = $this->add($data);
            if ($result) {
                /* 注册IM账号 */
                $IM_username = C('EASEMOB.EASEMOB_PREFIX') . $result;
                $IM_password = rand(10000000, 99999999);

                import('@.ORG.EasemobIMSDK');
                $rest = new \Hxcall();
                $IM_result = $rest->hx_register($IM_username, $IM_password, '');
                $IM_resultArr = json_decode($IM_result, true);
                $IM_uuid = $IM_resultArr['entities'][0]['uuid'];
                /* 更新用户IM信息 */
                if ($IM_uuid) {
                    $IM_data_info['IM_uuid'] = $IM_uuid;
                    $IM_data_info['IM_username'] = $IM_username;
                    $IM_data_info['IM_password'] = $IM_password;
                    $IM_data_info['display'] = 1;
                    $where_IM_info['id'] = array('EQ', $result);
                    $IM_result_info = $this->where($where_IM_info)->save($IM_data_info);
                    if ($IM_result_info) {
//                        /* 默认关注官方账号 */
//                        $data_attention['user_id'] = $result;
//                        $data_attention['to_user_id'] = 1;
//                        $data_attention['create_time'] = NOW_TIME;
//                        M('UserAttention')->add($data_attention);
//
//                        /* 注册成功累加小颜账号粉丝 */
//                        $this->where('id=1')->setInc('fans_count');

                        /* 成功推送IM */
                        import('Api.ORG.EasemobIMSDK');
                        $rest = new \Hxcall();
                        $sender = C('EASEMOB.EASEMOB_PREFIX') . '1';
                        $receiver = C('EASEMOB.EASEMOB_PREFIX') . $result;
                        $msg = L('TS_user_welcome');
                        $ext = array(
                            'type' => 5
                        );
                        $rest->hx_send($sender, $receiver, $msg, $ext);
                        return true;
                    }
                }
            }
        //}
        return false;
    }

    /**
     * 手机用户登录 do_login
     */
    public function do_login() {
        /* 定义变量 */
        if ($this->create('', self::MODEL_LOGIN)) {
            $auth_token = get_auth_token();
            $where['telephone'] = array('EQ', I('post.telephone'));
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $password = $this->where($where)->getField('password');
            /* 密码错误 */
            if ($password != MD5(I('post.password'))) {
                $this->error = L('YZ_password_error');
                return false;
            }
            /* 更新数据 */
            $data['client_system'] = I('post.client_system');
            $data['push_id'] = I('post.push_id');
            $data['auth_token'] = $auth_token;
            $result = $this->where($where)->save($data);
            return $result;
        }
        return false;
    }

    /**
     * 第三方平台用户登录 do_login_other
     */
    public function do_login_other() {
        /* 定义变量 */
        $wx_access_token = I('post.access_token');
        $wx_openid = I('post.open_id');

        if ($this->create('', self::MODEL_LOGIN_OTHER)) {
            /* 获取微信返回json数据 */
            $getJson = file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token=' . $wx_access_token . '&openid=' . $wx_openid . '&lang=zh_CN');
            $wxUserData = json_decode($getJson, true);
            if ($wxUserData && !$wxUserData['errcode']) {
                /* 定义变量 */
                $open_id = $wxUserData['openid'];
                $auth_token = get_auth_token();

                $where['user_type'] = array('EQ', 1);
                $where['open_id'] = array('EQ', $open_id);
                $where['display'] = array('GT', 0);
                $user = $this->where($where)->find();

                if ($user) { //登录
                    if ($user['status'] == 1) {
                        $where['status'] = array('EQ', 1);
                        /* 更新数据 */
                        $data_all['client_system'] = I('post.client_system');
                        $data['push_id'] = I('post.push_id');
                        $data['auth_token'] = $auth_token;
                        $result = $this->where($where)->save($data);
                        return $result;
                    }
                    $this->error = L('YZ_userStatus_error');
                } else { //注册新用户
                    /* 保存头像 */
                    $path = './Uploads/Images/User/' . date('Ymd');
                    mkdirss($path);
                    $img_name = time() . rand(1000, 9999) . '.jpg';
                    file_put_contents($path . '/' . $img_name, file_get_contents($wxUserData['headimgurl']));

                    /* 本地注册用户 */
                    $data['user_type'] = 1;
                    $data['open_id'] = $wxUserData['openid'];
                    $data['nick_name'] = $wxUserData['nickname'];
                    $data['sex'] = $wxUserData['sex'] !== 1 ? 2 : $wxUserData['sex'];
                    $data['upfile_head'] = date('Ymd') . '/' . $img_name;
                    $data['province'] = search_city($wxUserData['province']);
                    $data['city'] = search_city($wxUserData['city']);
                    $data['client_system'] = I('post.client_system');
                    $data['push_id'] = I('post.push_id');
                    $data['auth_token'] = $auth_token;
                    $data['create_time'] = NOW_TIME;

                    $result = $this->add($data);

                    if ($result) {
                        /* 注册IM账号 */
                        $IM_username = C('EASEMOB.EASEMOB_PREFIX') . $result;
                        $IM_password = rand(10000000, 99999999);

                        import('@.ORG.EasemobIMSDK');
                        $rest = new \Hxcall();
                        $IM_result = $rest->hx_register($IM_username, $IM_password, '');
                        $IM_resultArr = json_decode($IM_result, true);
                        $IM_uuid = $IM_resultArr['entities'][0]['uuid'];

                        /* 更新用户IM信息 */
                        if ($IM_uuid) {
                            $IM_data_info['IM_uuid'] = $IM_uuid;
                            $IM_data_info['IM_username'] = $IM_username;
                            $IM_data_info['IM_password'] = $IM_password;
                            $IM_data_info['display'] = 1;
                            $where_IM_info['id'] = array('EQ', $result);
                            $IM_result_info = $this->where($where_IM_info)->save($IM_data_info);
                            if ($IM_result_info) {
//                                /* 默认关注官方账号 */
//                                $data_attention['user_id'] = $result;
//                                $data_attention['to_user_id'] = 1;
//                                $data_attention['create_time'] = NOW_TIME;
//                                M('UserAttention')->add($data_attention);
//
//                                /* 注册成功累加小颜账号粉丝 */
//                                $this->where('id=1')->setInc('fans_count');

                                /* 成功推送IM */
                                import('Api.ORG.EasemobIMSDK');
                                $rest = new \Hxcall();
                                $sender = C('EASEMOB.EASEMOB_PREFIX') . '1';
                                $receiver = C('EASEMOB.EASEMOB_PREFIX') . $result;
                                $msg = L('TS_user_welcome');
                                $ext = array(
                                    'type' => 5
                                );
                                $rest->hx_send($sender, $receiver, $msg, $ext);

                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * 修改资料 do_edit_info
     */
    public function do_edit_info() {
        if ($this->create('', self::MODEL_EDIT_INFO)) {
            /* 定义变量 */
            $data = array();
            $user_id = I('post.user_id');
            $upfile_head = I('post.upfile_head');
            $dataList = explode(',', 'nick_name,sex,description,province,city,area');
            foreach ($dataList as $v) {
                $data[$v] = I('post.' . $v);
            }
            if ($upfile_head) {
                $file_name = get_upfile($upfile_head);
                $data['upfile_head'] = $file_name;
                
                
                $file_name_m = "";
                $strArr = explode('/', $file_name);
                for ($i= 0;$i< count($strArr); $i++){
                    if($i == (count($strArr)-1)){
                        $file_name_m = $file_name_m . 'm' . $strArr[$i] . '/';
                    }else{
                        $file_name_m =  $file_name_m . $strArr[$i] . '/';
                    }
                }
                $file_name_m = substr($file_name_m, 0, -1);
                $data['upfile_head_m'] = $file_name_m;


            }
            $where['id'] = array('EQ', $user_id);
            $result = $this->where($where)->save($data);
            return $result;
        }
        return false;
    }





    /**
     * 修改密码 do_edit_password
     * @param $data
     * @return bool
     */
    public function do_edit_password() {
        if ($this->create('', self::MODEL_EDIT_PASSWORD)) {
            $where['id'] = array('EQ', I('post.user_id'));
            $password = $this->where($where)->getField('password');
            /* 密码错误 */
            if ($password != MD5(I('post.old_password'))) {
                $this->error = L('YZ_password_error');
                return false;
            }
            /* 更新数据 */
            $data['password'] = MD5(I('post.password'));
            $result = $this->where($where)->save($data);
            return $result;
        }
        return false;
    }

    /**
     * 重置密码 do_password_reset
     * @param $data
     * @return bool
     */
    public function do_password_reset() {
        if ($this->create('', self::MODEL_RESET_PASSWORD)) {
            $where['telephone'] = array('EQ', I('post.telephone'));
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            /* 更新数据 */
            $data['password'] = MD5(I('post.password'));
            $result = $this->where($where)->save($data);
            return $result;
        }
        return false;
    }

    /**
     * 认证头像 do_authentication_upfile_head
     */
    public function do_authentication_upfile_head() {
        if ($this->create('', self::MODEL_RESET_AUTH_UPFILE_HEAD)) {
            $user_id = I('post.user_id');
            $where['id'] = array('EQ', $user_id);
            $where['upfile_head_auth_type'] = array('IN', '0,3');
            $data['upfile_head_auth'] = get_upfile(I('post.upfile_head_auth'));
            $data['upfile_head_auth_type'] = 2;
            $this->where($where)->save($data);
            return true;
        }
        return false;
    }

    /**
     * 查询用户 do_info
     */
    public function do_info() {
        /* 定义变量 */
        $user_id = I('get.user_id');
        $get_user_id = I('get.get_user_id');

        /* 查询数据 */
        $field = 'id,nick_name,sex,chat_level,chat_value,comment_notify,get_gold_notify,trace_notify,letter_notify,top_times,top_best,description,upfile_head,province,city,upfile_head_auth,upfile_head_auth_type,IM_username,like_count,like_now_count,like_consume_count,attention_count,fans_count,0 as is_be_shielded';
        $where['id'] = array('EQ', $get_user_id);
        $where['status'] = array('EQ', 1);
        $where['display'] = array('EQ', 1);
        $user = M('User')->field($field)->where($where)->find();

        if ($user) {
            /* 头像 */
            if ($user['upfile_head'] && !strstr($user['upfile_head'], 'http://')) {
                $user['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $user['upfile_head'];
            }
            /* 认证头像 */
            if ($user['upfile_head_auth']) {
                $user['upfile_head_auth'] = C('APP_URL') . '/Uploads/Images/User/' . $user['upfile_head_auth'];
            }
            /* 获取去向总数 */
            $whereUserWent['shop_user_went.user_id'] = array('EQ', $get_user_id);
            $whereUserWent['shop.status'] = array('EQ', 1);
            $whereUserWent['shop.display'] = array('EQ', 1);
            $user['userwent_count'] = M('ShopUserWent')
                ->alias('shop_user_went')
                ->where($whereUserWent)
                ->join('__SHOP__ shop on shop.id = shop_user_went.shop_id')
                ->count();
            /* 获取关注状态 */
            $attention_relation = get_user_attention($user_id, $get_user_id);
            $attention_relation = empty($attention_relation) ? strval(0) : $attention_relation;
            $user['attention_relation'] = $attention_relation;
            /* 获取屏蔽状态 */
            $blocked_relation = get_user_blocked($user_id, $get_user_id);
            $blocked_relation = empty($blocked_relation) ? strval(0) : $blocked_relation;
            $user['blocked_relation'] = $blocked_relation;

            /* 查询是否被屏蔽 */
            $where_is_be_shielded['user_id'] = array('EQ', $get_user_id);
            $where_is_be_shielded['to_user_id'] = array('EQ', $user_id);
            $count_is_be_shielded = M('UserBlocked')->where($where_is_be_shielded)->count();
            $user['is_be_shielded'] = $count_is_be_shielded;
        }

        /* 返回数据 */
        $return_data['data'] = arr_content_replace($user);
        return $return_data;
    }

    /**
     * 退出登录 do_logout
     * @return bool
     */
    public function do_logout() {
        $user_id = I('get.user_id');
        $where['id'] = array('EQ', $user_id);
        $data['auth_token'] = '';
        $data['client_system'] = '';
        $data['push_id'] = '';
        $result = $this->where($where)->save($data);
        return $result;
    }

    /**
     * 关注列表 do_attention_index
     * @param $data
     * @return bool
     */
    public function do_attention_index() {
        /* 定义变量 */
        $user_id = I('get.user_id');
        $get_user_id = I('get.get_user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        $field = 'user.id as user_id,user.nick_name,user.upfile_head,user.sex,user.like_count as user_like_count';
        $where['user_attention.user_id'] = array('EQ', $get_user_id);
        $where['user.status'] = array('EQ', 1);
        $where['user.display'] = array('EQ', 1);
        $order = 'user_attention.create_time desc';
        $list = M('UserAttention')
            ->alias('user_attention')
            ->field($field)
            ->where($where)
            ->join('__USER__ user on user_attention.to_user_id = user.id')
            ->order($order)
            ->limit(C('PAGE_NUM'))
            ->page($page_num)
            ->select();

        if ($list) {
            /* 遍历数据 */
            foreach ($list as $k => $v) {
                /* 读取用户头像 */
                if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                    $list[$k]['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
                }
            }

            /* 查询关注状态 */
            $list_user_id = array();
            foreach ($list as $k => $v) {
                $list_user_id[] = $v['user_id'];
            }
            if ($list_user_id) {
                $whereAttentionRelation['user_id'] = array('EQ', $user_id);
                $whereAttentionRelation['get_user_id'] = array('IN', implode(',', $list_user_id));
                $listAttentionRelation = M('UserAttention')->field('to_user_id,relation')->where($whereAttentionRelation)->select();
                /* 遍历合并数组 */
                foreach ($list as $k => $v) {
                    $list[$k]['attention_relation'] = strval(0);
                    foreach ($listAttentionRelation as $key => $value) {
                        if ($v['user_id'] == $value['to_user_id']) {
                            $list[$k]['attention_relation'] = $value['relation'];
                        }
                    }
                }
            }
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 粉丝列表 do_fans_index
     * @param $data
     * @return bool
     */
    public function do_fans_index() {
        /* 定义变量 */
        $user_id = I('get.user_id');
        $get_user_id = I('get.get_user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 查询数据 */
        $field = 'user.id as user_id,user.nick_name,user.upfile_head,user.sex,user.like_count as user_like_count';
        $where['user_attention.to_user_id'] = array('EQ', $get_user_id);
        $where['user.status'] = array('EQ', 1);
        $where['user.display'] = array('EQ', 1);
        $order = 'user_attention.create_time desc';
        $list = M('UserAttention')
            ->alias('user_attention')
            ->field($field)
            ->where($where)
            ->join('__USER__ user on user_attention.user_id = user.id')
            ->order($order)
            ->limit(C('PAGE_NUM'))
            ->page($page_num)
            ->select();

        /* 遍历数据 */
        foreach ($list as $k => $v) {
            /* 读取用户头像 */
            if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                $list[$k]['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
            }
        }

        if ($list) {
            /* 查询关注状态 */
            $list_user_id = array();
            foreach ($list as $k => $v) {
                $list_user_id[] = $v['user_id'];
            }
            if ($list_user_id) {
                $whereAttentionRelation['user_id'] = array('EQ', $user_id);
                $whereAttentionRelation['get_user_id'] = array('IN', implode(',', $list_user_id));
                $listAttentionRelation = M('UserAttention')->field('to_user_id,relation')->where($whereAttentionRelation)->select();
                /* 遍历合并数组 */
                foreach ($list as $k => $v) {
                    $list[$k]['attention_relation'] = strval(0);
                    foreach ($listAttentionRelation as $key => $value) {
                        if ($v['user_id'] == $value['to_user_id']) {
                            $list[$k]['attention_relation'] = $value['relation'];
                        }
                    }
                }
            }
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 获取用户 IM 信息 do_im_info
     */
    public function do_im_info() {
        /* 定义变量 */
        $IM_username = I('get.IM_username');
        $IM_username_arr = explode(',', $IM_username);

        $field = 'id as user_id,nick_name,upfile_head,IM_username';
        $where['IM_username'] = array('IN', $IM_username);
        $where['status'] = array('EQ', 1);
        $where['display'] = array('EQ', 1);
        $list = $this
            ->field($field)
            ->where($where)
            ->select();

        /* 遍历数据 */
        foreach ($list as $k => $v) {
            /* 读取用户头像 */
            if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                $list[$k]['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
            }
        }

        /* 遍历排序 */
        $listResult = array();
        foreach ($IM_username_arr as $k => $v) {
            foreach ($list as $kList => $vList) {
                if ($v == $vList['IM_username']) {
                    $listResult[] = $vList;
                }
            }
        }

        /* 读取json */
        $jsonInfo['list'] = arr_content_replace($listResult);
        return $jsonInfo;
    }

    /**
     * 搜索用户名
     * @param $data
     * @return bool
     */
    public function do_search_nickname($keywords_nickname = NULL) {
        /* 定义变量 */
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        $field = 'id,nick_name,sex,upfile_head,province,city,like_count';
        $where['nick_name'] = array('LIKE', '%' . $keywords_nickname . '%');
        $where['status'] = array('EQ', 1);
        $where['display'] = array('EQ', 1);
        $list = $this
            ->field($field)
            ->where($where)
            ->limit(C('PAGE_NUM'))
            ->page($page_num)
            ->select();
        /* 遍历数据 */
        foreach ($list as $k => $v) {
            /* 读取用户头像 */
            if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                $list[$k]['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
            }
        }

        /* 读取json */
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /* 自动验证和自动完成函数 */
    /* 注册验证验证码 validate_captcha */
    protected function validate_captcha($data) {
        if ($data) {
            /* 定义变量 */
            $type = ACTION_NAME;
            $telephone = I('post.telephone');
            $captchaCode = 'captchaCode_' . $telephone . '_' . $type;
            if ($data == S($captchaCode))
                return true;
        }
        return false;
    }

    /* 判断新旧密码是否一样 validate_updatePassword */
    protected function validate_updatePassword($data) {
        if (I('post.old_password') !== $data)
            return true;
        return false;
    }
}