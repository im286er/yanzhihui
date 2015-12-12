<?php
namespace Api\Model;

class UserAttentionModel extends CommonModel {
    /* 字段映射 */
const MODEL_ATTENTION_ADD = 4;

    /* 插入模型数据 操作状态 */
    const MODEL_ATTENTION_DELETE = 5; //关注
        protected $_map = array(); //取消关注

    /* 自动验证 */
    protected $_validate = array(
        /* 关注 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_ATTENTION_ADD),
        array('to_user_id', 'validate_toUserId_add_check', '{%YZ_attentionToUserId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_ATTENTION_ADD),
        /* 取消关注 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_ATTENTION_DELETE),
        array('to_user_id', 'validate_toUserId_delete_check', '{%YZ_attentionToUserId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_ATTENTION_DELETE)
    );
    /* 模型自动完成 */
    protected $_auto = array(
        /* 关注 */
        array('create_time', 'time', self::MODEL_ATTENTION_ADD, 'function'),
        array('relation', 'auto_relation_add', self::MODEL_ATTENTION_ADD, 'callback')
    );

    /**
     * 关注 do_add
     */
    public function do_add($user_id, $to_user_id) {
        if ($this->create('', self::MODEL_ATTENTION_ADD)) {
            $result = $this->add();
            if ($result) {
                /* User表累加关注数 */
                $whereAttention['id'] = array('EQ', $user_id);
                M('User')->where($whereAttention)->setInc('attention_count');
                /* User表累加粉丝数 */
                $whereFans['id'] = array('EQ', $to_user_id);
                M('User')->where($whereFans)->setInc('fans_count');
            }
            return $result;
        }
        return false;
    }

    /**
     * 取消关注 do_delete
     */
    public function do_delete($user_id, $to_user_id) {
        if ($this->create('', self::MODEL_ATTENTION_DELETE)) {
            $whereDel['user_id'] = array('EQ', $user_id);
            $whereDel['to_user_id'] = array('EQ', $to_user_id);
            $result = $this->where($whereDel)->delete();
            if ($result) {
                /* 更新关注关系 */
                $whereRelation['user_id'] = array('EQ', $to_user_id);
                $whereRelation['to_user_id'] = array('EQ', $user_id);
                $count = $this->where($whereRelation)->count();
                if ($count) {
                    $this->where($whereRelation)->setField('relation', 1);
                }
                /* User表累加关注数 */
                $whereAttention['id'] = array('EQ', $user_id);
                M('User')->where($whereAttention)->setDec('attention_count');
                /* User表累加粉丝数 */
                $whereFans['id'] = array('EQ', $to_user_id);
                M('User')->where($whereFans)->setDec('fans_count');
            }
            return $result;
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
    /* 验证关注ID validate_toUserId_add_check */
    protected function validate_toUserId_add_check($data) {
        $user_id = I('post.user_id');
        if ($data && $user_id !== $data) {
            $field = 'user.id,IFNULL(user_attention.user_id, 0) as is_attention';
            $where['user.id'] = array('EQ', $data);
            $where['user.status'] = array('EQ', 1);
            $where['user.display'] = array('EQ', 1);
            $to_user = M('User')
                ->alias('user')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __USER_ATTENTION__ user_attention on (user_attention.to_user_id = user.id AND user_attention.user_id = ' . $user_id . ')')
                ->find();
            if ($to_user['is_attention'] === '0')
                return true;
        }
        return false;
    }

    /* 验证取消关注ID validate_toUserId_delete_check */
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

    /* 自动获取关注关系 auto_relation_add */
    protected function auto_relation_add() {
        $user_id = I('post.user_id');
        $to_user_id = I('post.to_user_id');
        $where['user_id'] = array('EQ', $to_user_id);
        $where['to_user_id'] = array('EQ', $user_id);
        $count = $this->where($where)->count();
        if ($count) {
            /* 更改用户relation状态 */
            $this->where($where)->setField('relation', 2);
            return 2;
        }
        return 1;
    }
}