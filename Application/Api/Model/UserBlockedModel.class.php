<?php
namespace Api\Model;

class UserBlockedModel extends CommonModel {
    /* 插入模型数据 操作状态 */
    const MODEL_BLOCKED_ADD = 4; //屏蔽
    const MODEL_BLOCKED_DELETE = 5; //取消屏蔽

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 屏蔽 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_BLOCKED_ADD),
        array('to_user_id', 'validate_toUserId_add_check', '{%YZ_blockedToUserId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_BLOCKED_ADD),
        /* 取消屏蔽 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_BLOCKED_DELETE),
        array('to_user_id', 'validate_toUserId_delete_check', '{%YZ_blockedToUserId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_BLOCKED_DELETE)
    );
    /* 模型自动完成 */
    protected $_auto = array(
        /* 屏蔽 */
        array('create_time', 'time', self::MODEL_BLOCKED_ADD, 'function')
    );

    /**
     * 列表
     */
    public function do_index() {
        /* 定义变量 */
        $user_id = I('get.user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 查询数据 */
        $field = 'user.id as user_id,user.nick_name,user.upfile_head,user.sex';
        $where['user_blocked.user_id'] = array('EQ', $user_id);
        $where['user.status'] = array('EQ', 1);
        $where['user.display'] = array('EQ', 1);
        $order = 'user_blocked.create_time desc';
        $list = $this
            ->alias('user_blocked')
            ->field($field)
            ->where($where)
            ->join('__USER__ user on user_blocked.to_user_id = user.id')
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
            $list[$k]['IM_username'] = C('EASEMOB.EASEMOB_PREFIX') . $v['user_id'];
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 屏蔽 do_add
     */
    public function do_add() {
        if ($this->create('', self::MODEL_BLOCKED_ADD)) {
            $result = $this->add();
            return $result;
        }
        return false;
    }

    /**
     * 取消屏蔽 do_delete
     */
    public function do_delete() {
        if ($this->create('', self::MODEL_BLOCKED_DELETE)) {
            $whereDel['user_id'] = array('EQ', I('post.user_id'));
            $whereDel['to_user_id'] = array('EQ', I('post.to_user_id'));
            $result = $this->where($whereDel)->delete();
            return $result;
        }
        return false;
    }

    /**
     * 查询被对方用户屏蔽
     */
    public function do_be_shielded_user_index() {
        /* 定义变量 */
        $user_id = I('get.user_id');

        /* 查询数据 */
        $where['to_user_id'] = array('EQ', $user_id);
        $list = $this->where($where)->getField('user_id', true);

        /* 遍历数据 */
        foreach ($list as $k => $v) {
            $list[$k] = C('EASEMOB.EASEMOB_PREFIX') . $v;
        }

        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = $list;
        return $jsonInfo;
    }

    /* 自动验证和自动完成函数 */
    /* 验证屏蔽ID validate_toUserId_add_check */
    protected function validate_toUserId_add_check($data) {
        $user_id = I('post.user_id');
        if ($data && $user_id !== $data) {
            $field = 'user.id,IFNULL(user_blocked.user_id, 0) as is_blocked';
            $where['user.id'] = array('EQ', $data);
            $where['user.status'] = array('EQ', 1);
            $where['user.display'] = array('EQ', 1);
            $to_user = M('User')
                ->alias('user')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __USER_BLOCKED__ user_blocked on (user_blocked.to_user_id = user.id AND user_blocked.user_id = ' . $user_id . ')')
                ->find();
            if ($to_user['is_blocked'] === '0')
                return true;
        }
        return false;
    }

    /* 验证取消屏蔽ID validate_toUserId_delete_check */
    protected function validate_toUserId_delete_check($data) {
        if ($data) {
            $user_id = I('post.user_id');
            $where['user_id'] = array('EQ', $user_id);
            $where['to_user_id'] = array('EQ', $data);
            $count = $this->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }
}