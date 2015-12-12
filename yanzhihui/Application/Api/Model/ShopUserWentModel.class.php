<?php
namespace Api\Model;

class ShopUserWentModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 用户去向 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('shop_id', 'validate_shopId_check', '{%YZ_shopId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('type', array(1, 2), '{%YZ_shopLikeType_error}', self::MUST_VALIDATE, 'in', self::MODEL_INSERT)
    );
    /* 模型自动完成 */
    protected $_auto = array(
        /* 用户去向 */
        array('content', 'auto_content', self::MODEL_INSERT, 'callback'),
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /**
     * 去向列表 do_index
     */
    public function do_index() {
        /* 初始化变量 */
        $shop_id = I('get.shop_id');
        $user_id = I('get.get_user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 查询条件 */
        $field = 'user.id as user_id,user.upfile_head as user_upfile_head,user.nick_name,user.sex,user.province,user.city,
                      shop_user_went.shop_id,shop_user_went.content,shop_user_went.create_time,
                      shop.title as shop_title,shop.upfile as shop_upfile';
        if ($shop_id) {
            $where['shop_user_went.shop_id'] = array('EQ', $shop_id);
        }
        if ($user_id) {
            $where['shop_user_went.user_id'] = array('EQ', $user_id);
        }

        $where['user.status'] = array('EQ', 1);
        $where['user.display'] = array('EQ', 1);
        $where['shop.status'] = array('EQ', 1);
        $where['shop.display'] = array('EQ', 1);
        $order = 'shop_user_went.create_time desc';
        $list = $this
            ->alias('shop_user_went')
            ->field($field)
            ->where($where)
            ->join('__USER__ user on shop_user_went.user_id = user.id')
            ->join('__SHOP__ shop on shop.id = shop_user_went.shop_id')
            ->order($order)
            ->limit(C('PAGE_NUM'))
            ->page($page_num)
            ->select();
        /* 遍历数据 */
        foreach ($list as $k => $v) {
            /* 读取用户头像 */
            if ($v['user_upfile_head'] && !strstr($v['user_upfile_head'], 'http://')) {
                $list[$k]['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['user_upfile_head'];
            }
            /* 读取商家头像 */
            if ($v['shop_upfile']) {
                $list[$k]['shop_upfile'] = C('APP_URL') . '/Uploads/Images/Shop/' . $v['shop_upfile'];
            }
            /* 过滤数据 */
            $list[$k]['shop_title'] = htmlspecialchars_decode($v['shop_title']);
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 去向通知列表 do_index_notice
     */
    public function do_index_notice($user_id = NULL) {
        /* 初始化变量 */
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 获取关注用户 */
        $where_user_create_time['id'] = array('EQ', $user_id);
        $user_create_time = M('User')->where($where_user_create_time)->getField('create_time');

        $where_attention['user_id'] = array('EQ', $user_id);
        $attention_user_id = M('UserAttention')->where($where_attention)->getField('to_user_id', true);
        $attention_user_id = implode(',', $attention_user_id);

        if ($attention_user_id) {
            $field = 'user.id as user_id,user.upfile_head as user_upfile_head,user.nick_name,user.sex,
                      shop_user_went.shop_id,shop_user_went.content,shop_user_went.create_time,
                      shop.title as shop_title,shop.upfile as shop_upfile';

            $where['shop_user_went.user_id'] = array('IN', $attention_user_id);
            $where['shop_user_went.create_time'] = array('GT', $user_create_time);
            $where['user.status'] = array('EQ', 1);
            $where['user.display'] = array('EQ', 1);
            $where['shop.status'] = array('EQ', 1);
            $where['shop.display'] = array('EQ', 1);
            $order = 'shop_user_went.create_time desc';
            $list = $this
                ->alias('shop_user_went')
                ->field($field)
                ->where($where)
                ->join('__USER__ user on shop_user_went.user_id = user.id')
                ->join('__SHOP__ shop on shop.id = shop_user_went.shop_id')
                ->order($order)
                ->limit(C('PAGE_NUM'))
                ->page($page_num)
                ->select();

            /* 遍历数据 */
            foreach ($list as $k => $v) {
                /* 读取用户头像 */
                if ($v['user_upfile_head'] && !strstr($v['user_upfile_head'], 'http://')) {
                    $list[$k]['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['user_upfile_head'];
                }
                /* 读取商家头像 */
                if ($v['shop_upfile']) {
                    $list[$k]['shop_upfile'] = C('APP_URL') . '/Uploads/Images/Shop/' . $v['shop_upfile'];
                }
                /* 过滤数据 */
                $list[$k]['shop_title'] = htmlspecialchars_decode($v['shop_title']);
            }
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 商家我想去 do_add
     */
    public function do_add() {
        if ($this->create()) {
            $result = $this->add();
            if ($result) {
                /* Shop表累加 */
                $model = M('Shop');
                $where['id'] = array('EQ', I('post.shop_id'));
                if (I('post.type') == 1) { //我想去
                    $model->where($where)->setInc('want_count'); //想去
                } else {
                    $model->where($where)->setInc('been_count'); //去过
                }
            }
            return $result;
        }
        return false;
    }

    /**
     * 商家兑换
     */
    public function do_add_buy($shop_id, $user_id, $result) {
        $where_shop_coupon_info['id'] = array('EQ', $result);
        $shop_coupon_info = M('PayOrder')->where($where_shop_coupon_info)->getField('shop_coupon_info');
        $shop_coupon_info = json_decode($shop_coupon_info, true);
        $data['shop_id'] = $shop_id;
        $data['user_id'] = $user_id;
        $data['pay_order_id'] = $result;
        $data['content'] = '购买了"' . $shop_coupon_info['title'] . '"';
        $data['create_time'] = NOW_TIME;
        $result = $this->add($data);
        return $result;
    }

    /**
     * 读取通知
     */
    public function do_read() {
        $user_id = I('get.user_id');
        $now_time = NOW_TIME;
        /* 存入缓存 */
        S('SHOP_USER_WENT_TIME_USER_' . $user_id, $now_time);
        /* 存入数据库 */
        $where['id'] = array('EQ', $user_id);
        M('User')->where($where)->setField('shop_user_went_read_time', $now_time);
    }

    /**
     * 读取未读消息数量
     */
    public function do_unread_info() {
        /* 初始化变量 */
        $user_id = I('get.user_id');

        // 获取关注用户
        $where_attention['user_id'] = array('EQ', $user_id);
        $attention_user_id = M('UserAttention')->where($where_attention)->getField('to_user_id', true);
        $attention_user_id = implode(',', $attention_user_id);

        $cache = S('SHOP_USER_WENT_TIME_USER_' . $user_id);
        if (!$cache) {
            $where_user['id'] = array('EQ', $user_id);
            $cache = M('User')->where($where_user)->getField('shop_user_went_read_time');
            /* 存入缓存 */
            S('SHOP_USER_WENT_TIME_USER_' . $user_id, $cache);
        }
        $where['user_id'] = array('IN', $attention_user_id);
        $where['create_time'] = array('GT', $cache);
        $count = $this->where($where)->count();

        /* 获取最后一条消息 */
        $where_user_create_time['id'] = array('EQ', $user_id);
        $user_create_time = M('User')->where($where_user_create_time)->getField('create_time');

        $field_data = 'user.id as user_id,user.upfile_head as user_upfile_head,user.nick_name,user.sex,
                      shop_user_went.shop_id,shop_user_went.content,shop_user_went.create_time,
                      shop.title as shop_title,shop.upfile as shop_upfile';
        $where_data['shop_user_went.user_id'] = array('IN', $attention_user_id);
        $where['shop_user_went.create_time'] = array('GT', $user_create_time);
        $order_data = 'shop_user_went.create_time desc';

        $list = $this
            ->alias('shop_user_went')
            ->field($field_data)
            ->where($where_data)
            ->join('__USER__ user on shop_user_went.user_id = user.id')
            ->join('__SHOP__ shop on shop.id = shop_user_went.shop_id')
            ->order($order_data)
            ->limit(1)
            ->select();

        foreach ($list as $k => $v) {
            /* 读取用户头像 */
            if ($v['user_upfile_head'] && !strstr($v['user_upfile_head'], 'http://')) {
                $list[$k]['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['user_upfile_head'];
            }
            /* 读取商家头像 */
            if ($v['shop_upfile']) {
                $list[$k]['shop_upfile'] = C('APP_URL') . '/Uploads/Images/Shop/' . $v['shop_upfile'];
            }
        }

        $result = array(
            'count' => strval($count),
            'data'  => empty($list) ? array() : $list
        );

        return $result;
    }

    /* 自动验证和自动完成函数 */
    /* 验证商家ID validate_shopId_check */
    protected function validate_shopId_check($data) {
        if ($data) {
            $user_id = I('post.user_id');
            $content = I('post.type') == 1 ? '想去' : '去过'; //1:我想去, 2:我去过

            $field = 'IFNULL(shop_user_went.user_id, 0) as is_like';
            $where['shop.id'] = array('EQ', $data);
            $where['shop.status'] = array('EQ', 1);
            $where['shop.display'] = array('EQ', 1);
            $shop = M('Shop')
                ->alias('shop')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __SHOP_USER_WENT__ shop_user_went on (shop_user_went.shop_id = shop.id AND shop_user_went.user_id = ' . $user_id . ' AND shop_user_went.content = "' . $content . '")')
                ->find();

            if ($shop['is_like'] === '0')
                return true;
        }
        return false;
    }

    /* 自动完成去向内容 */
    protected function auto_content() {
        $content = I('post.type') == 1 ? '想去' : '去过'; //1:我想去, 2:我去过
        return $content;
    }
}