<?php
namespace Api\Model;

class TopicCommentModel extends CommonModel {
    /* 插入模型数据 操作状态 */
    const MODEL_COMMENT_ADD = 4; //发布评论
    const MODEL_COMMENT_DELETE = 5;//删除评论
    const MODEL_COMMENT_DELETE_MYSELF = 6; //删除自己话题下的评论
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 发布评论 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_ADD),
        array('topic_id', 'validate_topicId_check', '{%YZ_topicId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_ADD),
        array('topic_id', 'validate_userId_blocked_check', '{%YZ_userId_error1}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_ADD),
        array('response_user_id', 'validate_responseUserId_check', '{%YZ_responseUserId_error}', self::VALUE_VALIDATE, 'callback', self::MODEL_COMMENT_ADD),
        array('content', 'require', '{%YZ_content_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_COMMENT_ADD),
        /* 删除评论 */
        array('comment_id', 'validate_topicCommentId_check', '{%YZ_topicCommentId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_DELETE),
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_DELETE),
        array('topic_id', 'validate_topicId_check', '{%YZ_topicId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_DELETE),
        /* 删除自己话题下的评论 */
        array('topic_id', 'validate_topicId_check', '{%YZ_topicId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_DELETE_MYSELF),//验证话题是否存在
        array('topic_id', 'validate_topicId_check_myself', '{%YZ_topicId_myself_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_DELETE_MYSELF),//验证是否属于自己活题
        array('comment_id', 'validate_topicCommentId_check_myself', '{%YZ_topicCommentId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_COMMENT_DELETE_MYSELF) //验证是否自己话题下的评论
    );
    /* 模型自动完成 */
    protected $_auto = array(
        /* 发布评论 */
        array('create_time', 'time', self::MODEL_COMMENT_ADD, 'function')
    );

    /**
     * 话题评论列表 do_index
     */
    public function do_index() {
        /* 初始化变量 */
        $topic_id = I('get.topic_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 查询条件 */
        $field = 'topic_comment.id,topic_comment.content,topic_comment.create_time,
                  user.id as user_id,user.nick_name,user.upfile_head,
                  IFNULL(response_user.id, "") as response_user_id,IFNULL(response_user.nick_name, "") as response_name';
        $where['topic_comment.topic_id'] = array('EQ', $topic_id);
        $where['topic_comment.status'] = array('EQ', 1);
        $where['topic_comment.display'] = array('EQ', 1);
        $order = 'topic_comment.id asc';
        $list = $this
            ->alias('topic_comment')
            ->field($field)
            ->where($where)
            ->join('__USER__ user on topic_comment.user_id = user.id AND user.status = 1  AND user.display = 1')
            ->join('LEFT JOIN __USER__ response_user on (topic_comment.response_user_id = response_user.id AND response_user.status = 1  AND response_user.display = 1)')
            ->order($order)
            ->limit(C('PAGE_NUM'))
            ->page($page_num)
            ->select();

        $list_count = $this
            ->alias('topic_comment')
            ->where($where)
            ->join('__USER__ user on topic_comment.user_id = user.id AND user.status = 1  AND user.display = 1')
            ->count();

        /* 遍历数据 */
        foreach ($list as $k => $v) {
            /* 读取用户头像 */
            if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                $list[$k]['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
            }
        }
        $jsonInfo['list_count'] = intval($list_count);

        /* 读取json */
        $list = empty($list) ? array() : $list;
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 发布话题评论 do_add
     */
    public function do_add() {
        if ($this->create('', self::MODEL_COMMENT_ADD)) {
            $result = $this->add();
            /* 评论成功 */
            if ($result) {
                /* 话题表累加评论数 */
                $where['id'] = array('EQ', I('post.topic_id'));
                M('Topic')->where($where)->setInc('comment_count');

                /* 用户表累加发布数 */
                $where['id'] = array('EQ', I('post.user_id'));
                M('User')->where($where)->setInc('topic_comment_count');
            }
            return $result;
        }
        return false;
    }

    /**
     * 删除话题评论 do_delete
     */
    public function do_delete() {
        if ($this->create('', self::MODEL_COMMENT_DELETE)) {
            $where['id'] = array('EQ', I('post.comment_id'));
            $result = $this->where($where)->setField('display', 0);
            /* 删除成功 */
            if ($result) {
                /* 话题表累减评论数 */
                $whereTopic['id'] = array('EQ', I('post.topic_id'));
                M('Topic')->where($whereTopic)->setDec('comment_count');

                /* 用户表累减发布数 */
                $where['id'] = array('EQ', I('post.user_id'));
                M('User')->where($where)->setInc('topic_comment_count');
            }
            return $result;
        }
        return false;
    }

    /**
     * 删除别人的评论 do_deletefans
     */
    public function do_deletefans() {
        if ($this->create('', self::MODEL_COMMENT_DELETE_MYSELF)) {
            $where['id'] = array('EQ', I('post.comment_id'));
            $result = $this->where($where)->setField('display', 0);
            /* 删除成功 */
            if ($result) {
                /* 话题表累减评论数 */
                $whereTopic['id'] = array('EQ', I('post.topic_id'));
                M('Topic')->where($whereTopic)->setDec('comment_count');

                /* 用户表累减发布数 */
                $where['id'] = array('EQ', I('post.user_id'));
                M('User')->where($where)->setInc('topic_comment_count');
            }
            return $result;
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
    /* 验证用户ID validate_responseUserId_check */

    public function validate_topicId_check_myself($data) {
        if ($data) {
            $user_id = I('get.user_id'); //登陆id
            $topic_id = I('post.topic_id');
            /* 查询数据 */
            $where['id'] = array('EQ', $data);
            $where['user_id'] = array('EQ', $user_id);
            $where['topic_id'] = array('EQ', $topic_id);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('Topic')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }

    /* 验证用户ID是否被屏蔽 */

    public function validate_topicCommentId_check_myself($data) {
        if ($data) {
            /* 定义变量 */
            $topic_id = I('post.topic_id');//话题id
            $user_id = I('post.user_id');
            /* 查询数据 */
            $where['id'] = array('EQ', $data);//话题id
            $where['topic_id'] = array('EQ', $topic_id);
            $where['user_id'] = array('EQ', $user_id);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = $this->where($where)->count();

            if ($count == 1)
                return true;
        }
        return false;
    }

    /* 验证评论ID validate_topicCommentId_check */

    protected function validate_responseUserId_check($data) {
        if ($data && I('post.user_id') !== $data) {
            $where['id'] = array('EQ', $data);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('User')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }

    /* 判断是否为自己的话题 */

    protected function validate_userId_blocked_check($data) {
        //todo
    }

    /* 判断是否为自己的话题下的评论 */

    protected function validate_topicCommentId_check($data) {
        if ($data) {
            /* 定义变量 */
            $user_id = I('post.user_id');
            $topic_id = I('post.topic_id');
            /* 查询数据 */
            $where['id'] = array('EQ', $data);
            $where['user_id'] = array('EQ', $user_id);
            $where['topic_id'] = array('EQ', $topic_id);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = $this->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }
}