<?php
namespace Api\Model;

use Think\Model;

class CommonModel extends Model {
    /* 自动验证和自动完成函数 */
    /* 验证用户ID validate_userId_check */
    protected function validate_userId_check($data) {
        if ($data && $data == I('get.user_id'))
            return true;
        return false;
    }

    /* 注册验证手机号码 validate_telephone_noExist */
    protected function validate_telephone_noExist($data) {
        if ($data) {
            $where['telephone'] = array('EQ', $data);
            $where['display'] = array('EQ', 1);
            $count = M('User')->where($where)->count();
            if (!$count)
                return true;
        }
        return false;
    }

    /* 忘记密码验证手机号码 validate_telephone_exist */
    protected function validate_telephone_exist($data) {
        if ($data) {
            $where['telephone'] = array('EQ', $data);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('User')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }

    /* 验证话题ID validate_topicId_check */
    protected function validate_topicId_check($data) {
        if ($data) {
            $where['id'] = array('EQ', $data);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('Topic')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }

    /* 验证商家ID validate_shopId_check */
    protected function validate_shopId_check($data) {
        if ($data) {
            $where['id'] = array('EQ', $data);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('Shop ')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }
}