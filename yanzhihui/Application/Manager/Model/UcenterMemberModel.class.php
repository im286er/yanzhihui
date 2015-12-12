<?php
namespace Manager\Model;

use Think\Model;

class UcenterMemberModel extends Model {
    /* 关闭字段自动检测 */
    protected $autoCheckFields = false;

    /* 表名 */
    protected $tableName = 'Member';

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
            $result = 0;
            /* 获取用户数据 */
            $where['username'] = array('EQ', $username);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $member = $this->field('id,password')->where($where)->find();
            if (!$member) {
                $result = array('msg' => L('YZ_member_not_exist'));
            } else {
                if ($member['id'] != C('MANAGER_ADMINISTRATOR')) {
                    $whereGroup['auth_group_access.user_id'] = array('EQ', $member['id']);
                    $whereGroup['auth_group.status'] = array('EQ', 1);
                    $whereGroup['auth_group.display'] = array('EQ', 1);
                    $countGroup = M('AuthGroup')
                        ->alias('auth_group')
                        ->where($whereGroup)
                        ->join('__AUTH_GROUP_ACCESS__ auth_group_access ON auth_group.id = auth_group_access.group_id')
                        ->count();
                    if ($countGroup < 1) {
                        $result = array('msg' => L('YZ_member_not_exist'));
                    }
                }
                if ($result == 0) {
                    if ($member['password'] == md5($password)) {
                        $this->update_login($member['id'], $username);
                        /* 登录成功，返回用户ID */
                        $result = array('result' => 1);
                    } else {
                        $result = array('msg' => L('YZ_password_error'));
                    }
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
            /* 读取用户权限 */
            $authRules = array();
            if ($uid != C('MANAGER_ADMINISTRATOR')) {
                $Auth = new \Manager\ORG\Auth();
                $authList = $Auth->getAuthList($uid, 1);
                foreach ($authList as $k => $v) {
                    $authRules[] = $v['menu_id'];
                }
            }
            session('auth_rules', array_unique($authRules));
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