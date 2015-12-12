<?php
namespace Manager\Model;
class MemberModel extends CommonModel {
    /* 插入模型数据 操作状态 */
    const MODEL_UPDATE_PASSWORD = 4;

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        array('username', 'require', '{%YZ_enter_username}', self::EXISTS_VALIDATE),
        array('username', 'validate_username_unique', '{%YZ_username_unique_error}', self::MUST_VALIDATE, 'callback'),
        array('password', 'require', '{%YZ_enter_password}', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('password', '6,30', '{%YZ_password_length}', self::VALUE_VALIDATE, 'length'),
        array('password', 'validate_password', '{%YZ_oldpassword_different_password}', self::VALUE_VALIDATE, 'callback'),
        /* 修改密码时验证 */
        array('oldpassword', 'validate_oldpassword', '{%YZ_oldpassword_error}', self::MUST_VALIDATE, 'callback', self::MODEL_UPDATE_PASSWORD),
        array('password', 'require', '{%YZ_enter_password}', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE_PASSWORD),
        array('password', 'validate_updatepassword', '{%YZ_oldpassword_different_password}', self::VALUE_VALIDATE, 'callback', self::MODEL_UPDATE_PASSWORD),
        array('repassword', 'require', '{%YZ_enter_repassword}', self::MUST_VALIDATE, 'regex', self::MODEL_UPDATE_PASSWORD),
        array('repassword', 'password', '{%YZ_password_different_repassword}', self::MUST_VALIDATE, 'confirm', self::MODEL_UPDATE_PASSWORD)
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('password', 'md5', self::MODEL_INSERT, 'function'),
        array('password', 'auto_update_password', self::MODEL_UPDATE, 'callback'),
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /* 新增和编辑数据的时候允许写入字段 */
    protected $insertFields = 'username,password';
    protected $updateFields = 'id,username,password,oldpassword,repassword';

    /* 数据操作 */
    public function do_update_password() {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                if ($this->create('', 4)) {
                    $where['id'] = session('user_id');
                    $result = $this->where($where)->setField('password', MD5(I('post.password')));
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