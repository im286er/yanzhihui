<?php
namespace Api\Model;

class PayOrderModel extends CommonModel {
    /* 插入模型数据 操作状态 */

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 购买优惠劵 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('coupon_id', 'validate_couponId_check', '{%YZ_couponId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('user_id', 'validate_userId_count_like_check', '{%YZ_userId_countLike_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('shop_id', 'require', '{%YZ_shopId_error}', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT)
    );
    /* 模型自动完成 */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );

    /**
     * 我的兑换 do_personal_index
     */
    public function do_personal_index() {
        /* 初始化变量 */
        $user_id = I('get.user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 查询条件 */
        $field = 'pay_order.trade_no,pay_order.shop_coupon_info,pay_order.shop_id,pay_order.trade_state,shop.title as shop_title';
        $where['pay_order.user_id'] = array('EQ', $user_id);
        $where['pay_order.display'] = array('EQ', 1);
        $order = 'pay_order.id desc';
        /* 查询数据 */
        $list = $this
            ->alias('pay_order')
            ->field($field)
            ->where($where)
            ->join('LEFT JOIN __SHOP__ shop on pay_order.shop_id = shop.id')
            ->order($order)
            ->limit(C('PAGE_NUM'))
            ->page($page_num)
            ->select();
        foreach ($list as $k => $v) {
            $shop_coupon_info = json_decode($v['shop_coupon_info'], true);
            $list[$k]['coupon_id'] = $shop_coupon_info['id'];
            $list[$k]['coupon_title'] = $shop_coupon_info['title'];
            if ($shop_coupon_info['coupon_type'] == 1) {
                $list[$k]['coupon_tag'] = strval(0);
            } else {
                $list[$k]['coupon_tag'] = $shop_coupon_info['coupon_worth'];
            }
            $list[$k]['coupon_content'] = $shop_coupon_info['content'];

            unset($list[$k]['shop_coupon_info']);

            /* 过滤数据 */
            if (!empty($v['coupon_title'])) {
                $list[$k]['coupon_title'] = htmlspecialchars_decode($v['coupon_title']);
            }
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 购买优惠劵 do_add
     */
    public function do_add() {
        if ($this->create()) {
            /* 定义变量 */
            $order_num = date('YmdHis', time());
            $order_rand = rand(10000, 99999);
            $user_id = I('post.user_id');
            $coupon_id = I('post.coupon_id');
            /* 读取优惠劵信息 */
            $whereCoupon['id'] = array('EQ', $coupon_id);
            $whereCoupon['status'] = array('EQ', 1);
            $whereCoupon['display'] = array('EQ', 1);
            $coupon = M('ShopCoupon')->where($whereCoupon)->find();
            $coupon_info = json_encode($coupon);

            /* 添加优惠劵 */
            $this->startTrans(); //开启事务

            $dataPayOrderAdd['order_id'] = $order_num . $order_rand;
            $dataPayOrderAdd['trade_no'] = dec36($order_num) . dec36($order_rand);
            $dataPayOrderAdd['user_id'] = $user_id;
            $dataPayOrderAdd['shop_account_id'] = $coupon['shop_account_id'];
            $dataPayOrderAdd['shop_id'] = $coupon['shop_id'];
            $dataPayOrderAdd['shop_coupon_id'] = $coupon_id;
            $dataPayOrderAdd['shop_coupon_like_consume_count'] = $coupon['like_consume_count'];
            $dataPayOrderAdd['shop_coupon_info'] = $coupon_info;
            $dataPayOrderAdd['create_time'] = NOW_TIME;
            $resultPayOrderAdd = $this->add($dataPayOrderAdd);

            /* 查看用户信息 */
            $whereUserCountLike['id'] = array('EQ', $user_id);
            $whereUserCountLike['status'] = array('EQ', 1);
            $whereUserCountLike['display'] = array('EQ', 1);
            $userData = M('User')->field('like_now_count,like_consume_count')->where($whereUserCountLike)->find();

            /* 修改用户颜值 */
            $dataUserCountLike['like_now_count'] = $userData['like_now_count'] - $coupon['like_consume_count'];
            $dataUserCountLike['like_consume_count'] = $userData['like_consume_count'] + $coupon['like_consume_count'];
            $resultUserCountLike = M('User')->where($whereUserCountLike)->save($dataUserCountLike);

            if ($resultPayOrderAdd && $resultUserCountLike) {
                $this->commit();//提交事务
                return $resultPayOrderAdd;
            } else {
                $this->rollback(); //事务回滚
            }
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
    /* 验证用户颜币是否够兑换次优惠劵 validate_userId_count_like_check */
    protected function validate_userId_count_like_check($data) {
        /* 需要兑换的颜币 */
        $coupon_id = I('post.coupon_id');
        $whereCoupon['id'] = array('EQ', $coupon_id);
        $whereCoupon['status'] = array('EQ', 1);
        $whereCoupon['display'] = array('EQ', 1);
        $like_consume_count = M('ShopCoupon')->where($whereCoupon)->getField('like_consume_count');
        /* 该用户目前的颜币 */
        $whereUser['id'] = array('EQ', $data);
        $whereUser['status'] = array('EQ', 1);
        $whereUser['display'] = array('EQ', 1);
        $user_like_now_count = M('User')->where($whereUser)->getField('like_now_count');
        if ($user_like_now_count >= $like_consume_count && !empty($like_consume_count)) {
            return true;
        }
        return false;
    }

    /* 验证优惠劵ID validate_couponId_check */
    protected function validate_couponId_check($data) {
        if ($data) {
            $where['id'] = array('EQ', $data);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('ShopCoupon')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }
}