<?php
namespace Manager\Model;
class ShopAccountModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        array('username', 'require', '{%YZ_enter_username}', self::EXISTS_VALIDATE),
        array('username', 'validate_username_unique', '{%YZ_username_unique_error}', self::MUST_VALIDATE, 'callback'),
        array('password', 'require', '{%YZ_enter_password}', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('password', '6,30', '{%YZ_password_length}', self::VALUE_VALIDATE, 'length'),
        array('password', 'validate_password', '{%YZ_oldpassword_different_password}', self::VALUE_VALIDATE, 'callback')
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('password', 'md5', self::MODEL_INSERT, 'function'),
        array('password', 'auto_update_password', self::MODEL_UPDATE, 'callback'),
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /* 自动验证和自动完成函数 */
    protected function validate_username_unique($data) {
        $id = I('post.id');
        $where['id'] = array('NEQ', $id);
        $where['username'] = array('EQ', $data);
        $where['display'] = array('EQ', 1);
        $count = $this->where($where)->count();
        if (empty($count)) {
            return true;
        }
        return false;
    }

    protected function validate_password($data) {
        $where['id'] = array('EQ', I('post.id'));
        $password = $this->where($where)->getField('password');
        if ($password !== md5($data)) {
            return true;
        }
        return false;
    }

    protected function auto_update_password($data) {
        $password = MD5($data);
        if (!$data) {
            $id = I('post.id');
            $where['id'] = array('EQ', $id);
            $password = $this->where($where)->getField('password');
        }
        return $password;
    }
}