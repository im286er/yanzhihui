<?php
namespace Account\Model;

use Think\Model;

class UcenterMemberModel extends Model {
    /* 关闭字段自动检测 */
    protected $autoCheckFields = false;

    /* 表名 */
    protected $tableName = 'shop_account';

    /* 自动验证规则 */
    protected $_validate = array(
        array('username', 'require', '{%YZ_enter_username}', self::MUST_VALIDATE),
        array('password', 'require', '{%YZ_enter_password}', self::MUST_VALIDATE),
        array('verify', 'require', '{%YZ_verify_error}', self::MUST_VALIDATE),
        array('verify', 'validate_verify_check', '{%YZ_verify_error}', self::MUST_VALIDATE, 'callback')
    );

    /**
     * 用户登陆
     * @param $username
     * @param $password
     * @return int
     */
    public function login() {
        if (!$this->create()) {
            $msg = $this->getError();
            $result = array('msg' => $msg);
        } else {
            /* 定义变量 */
            $username = I('post.username');
            $password = I('post.password');
            /* 获取用户数据 */
            $where['username'] = array('EQ', $username);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $member = $this->field('id,password')->where($where)->find();
            if (!$member) {
                $result = array('msg' => L('YZ_member_not_exist'));
            } else {
                if ($member['password'] == md5($password)) {
                    $this->update_login($member['id'], $username);
                    /* 登录成功，返回用户ID */
                    $result = array('result' => 1);
                } else {
                    $result = array('msg' => L('YZ_password_error'));
                }
            }
        }
        return $result;
    }

    /**
     * 更新用户登录信息
     * @param $id
     * @param $username
     */
    protected function update_login($uid = NULL, $username = NULL) {
        if ($uid && $username) {
            session('user_id', $uid);
            session('user_name', $username);
            logs_action_operate('登陆成功');
        }
        return false;
    }

    /* 自动验证函数 */
    protected function validate_verify_check($data) {
        $verify = new \Manager\ORG\Verify();
        if ($verify->check($data)) {
            return true;
        }
        return false;
    }
}