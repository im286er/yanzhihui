<?php
namespace Api\Model;

class TopicLikeModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 话题点赞 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('topic_id', 'validate_topicId_check', '{%YZ_topicId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT)
    );
    /* 模型自动完成 */
    protected $_auto = array(
        /* 话题点赞 */
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /**
     * 点赞列表 do_index
     */
    public function do_index() {
        /* 初始化变量 */
        $topic_id = I('get.topic_id');
        $user_id = I('get.user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 查询条件 */
        $field = 'user.id as user_id,user.upfile_head,user.nick_name,user.sex,user.like_count as user_like_count,0 as attention_relation';
        $where['topic_like.topic_id'] = array('EQ', $topic_id);
        $where['user.status'] = array('EQ', 1);
        $where['user.display'] = array('EQ', 1);
        $order = 'topic_like.create_time desc';
        $list = $this
            ->alias('topic_like')
            ->field($field)
            ->where($where)
            ->join('__USER__ user on topic_like.user_id = user.id')
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
        }

        /* 查询关注状态 */
        if ($user_id) {
            $whereAttentionRelation['user_id'] = array('EQ', $user_id);
            $listAttentionRelation = M('UserAttention')->field('to_user_id,relation')->where($whereAttentionRelation)->select();
            /* 遍历合并数组 */
            foreach ($list as $k => $v) {
                foreach ($listAttentionRelation as $key => $value) {
                    if ($v['user_id'] == $value['to_user_id']) {
                        $list[$k]['attention_relation'] = $value['relation'];
                    }
                }
            }
        }

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 话题点赞 do_add
     */
    public function do_add() {
        if ($this->create()) {
            $result = $this->add();
            if ($result) {
                $model = M('Topic');
                $where['id'] = array('EQ', I('post.topic_id'));
                $topic = $model->field('user_id,like_count')->where($where)->find();
                /* Topic表累加点赞数 */
                $model->where($where)->setField('like_count', $topic['like_count'] + 1);
                /* User表累加点赞数 */
                $where['id'] = array('EQ', $topic['user_id']);
                $userInfo = M('User')->field('like_count,like_now_count')->where($where)->find();
                $userData['like_count'] = $userInfo['like_count'] + 1;
                $userData['like_now_count'] = $userInfo['like_now_count'] + 1;
                M('User')->where($where)->save($userData);
                /* 用户点赞数累加 */
                $whereUserTopicLikeCount['id'] = array('EQ', I('post.user_id'));
                M('User')->where($whereUserTopicLikeCount)->setInc('topic_like_count');
            }
            return $result;
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
    /* 验证话题ID validate_topicId_check */
    protected function validate_topicId_check($data) {
        if ($data) {
            $user_id = I('post.user_id');
            $field = 'IFNULL(topic_like.user_id, 0) as is_like';
            $where['topic.id'] = array('EQ', $data);
            $where['topic.status'] = array('EQ', 1);
            $where['topic.display'] = array('EQ', 1);
            $topic = M('Topic')
                ->alias('topic')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __TOPIC_LIKE__ topic_like on (topic_like.topic_id = topic.id AND topic_like.user_id = ' . $user_id . ')')
                ->find();
            if ($topic['is_like'] === '0')
                return true;
        }
        return false;
    }
}