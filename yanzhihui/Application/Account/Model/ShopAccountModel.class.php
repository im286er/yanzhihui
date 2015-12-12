<?php
namespace Account\Model;
class ShopAccountModel extends CommonModel {
    /* 字段映射 */
    const MODEL_UPDATE_PASSWORD = 4;

    /* 插入模型数据 操作状态 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        /* 修改密码时验证 */
        array('oldpassword', 'validate_oldpassword', '{%YZ_oldpassword_error}', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE_PASSWORD),
        array('password', 'require', '{%YZ_enter_password}', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE_PASSWORD),
        array('password', 'validate_updatepassword', '{%YZ_oldpassword_different_password}', self::VALUE_VALIDATE, 'callback', self::MODEL_UPDATE_PASSWORD),
        array('repassword', 'require', '{%YZ_enter_repassword}', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE_PASSWORD),
        array('repassword', 'password', '{%YZ_password_different_repassword}', self::MUST_VALIDATE, 'confirm', self::MODEL_UPDATE_PASSWORD)
    );

    /* 模型自动完成 */
    protected $_auto = array();

    /* 新增和编辑数据的时候允许写入字段 */

    /* 数据操作 */
    public function do_update_password() {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                if ($this->create('', self::MODEL_UPDATE_PASSWORD)) {
                    $where['id'] = session('user_id');
                    $result = $this->where($where)->setField('password', md5(I('post.password')));
                }
            } catch (\Exception $e) {
                $remark = $e->getMessage();
                /* 记录操作异常日志 */
                logs_system_error($remark);
            }
        }
        return $result;
    }

    /* 自动验证和自动完成函数 */
    protected function validate_oldpassword($data) {
        $where['id'] = array('EQ', session('user_id'));
        $password = $this->where($where)->getField('password');
        if ($password == md5($data)) {
            return true;
        }
        return false;
    }

    protected function validate_updatepassword($data) {
        if (I('post.oldpassword') !== $data) {
            return true;
        }
        return false;
    }
}