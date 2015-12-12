<?php
namespace Account\Model;
class ShopCouponModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 插入模型数据 操作状态 */

    /* 自动验证规则 */
    protected $_validate = array(
        array('title', 'require', '{%YZ_enter_title}', self::MUST_VALIDATE, 'regex'),
        array('title', 'validate_title_unique', '{%YZ_title_unique_error}', self::MUST_VALIDATE, 'callback'),
        array('shop_id', 'validate_shopId', '{%YZ_enter_shopId}', self::MUST_VALIDATE, 'callback'),
        array('coupon_type', array(1, 2), '{%YZ_enter_couponType}', self::MUST_VALIDATE, 'in'),
        array('coupon_worth', 'validate_couponWorth', '{%YZ_enter_couponWorth}', self::MUST_VALIDATE, 'callback'),
        array('like_consume_count', 'require', '{%YZ_enter_likeConsumeCount}', self::MUST_VALIDATE, 'regex'),
        array('like_consume_count', 'is_positive_int', '{%YZ_enter_likeConsumeCount}', self::MUST_VALIDATE, 'function'),
        array('content', 'require', '{%YZ_enter_title}', self::MUST_VALIDATE, 'regex')
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('status', '0', self::MODEL_BOTH),
        array('shop_account_id', UID, self::MODEL_INSERT),
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function')
    );

    /* 新增和编辑数据的时候允许写入字段 */
    protected $insertFields = 'title,shop_id,coupon_type,coupon_worth,like_consume_count,content';
    protected $updateFields = 'id,title,shop_id,coupon_type,coupon_worth,like_consume_count,content';

    /* 数据操作 */

    /* 自动验证和自动完成函数 */
    /* 验证标题重复 validate_title_unique */
    protected function validate_title_unique($data) {
        $id = I('post.id');
        $shop_id = I('post.shop_id');

        $where['id'] = array('NEQ', $id);
        $where['shop_id'] = array('EQ', $shop_id);
        $where['title'] = array('EQ', $data);
        $where['display'] = array('EQ', 1);
        $count = $this->where($where)->count();
        if (empty($count)) {
            return true;
        }
        return false;
    }

    /* 验证门店ID validate_shopId */
    protected function validate_shopId($data) {
        $whereShop['shop_account_id'] = array('EQ', UID);
        $whereShop['display'] = array('EQ', 1);
        $shop_id = M('Shop')->field('id,title')->where($whereShop)->getField('id', true);
        if (in_array($data, $shop_id)) {
            return true;
        }
        return false;
    }

    /* 验证抵扣价值 */
    protected function validate_couponWorth($data) {
        $coupon_type = I('post.coupon_type');
        if ($coupon_type == 1) {
            return true;
        }
        if ($coupon_type == 2 && is_positive_int($data) && $data > 0 || $coupon_type == 0) {
            return true;
        }
        return false;
    }
}