<?php
namespace Manager\Controller;

use Think\Controller;

class TopicCommentController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = CONTROLLER_NAME;
        $getTitle = I('get.title');
        /* 查询条件 */
        $field = 'topic_comment.id,topic_comment.content,topic_comment.status,topic_comment.create_time,
                  topic.content as topic_content,
                  user.nick_name as user_nick_name,
                  response_user.nick_name as user_response_nick_name';
        $where['topic_comment.display'] = array('EQ', 1);
        $where['topic.display'] = array('EQ', 1);
        /* 搜索条件 */
        if ($getTitle) {
            $where['topic_comment.content'] = array('LIKE', '%' . $getTitle . '%');
        }
        /* 查询排序 */
        $order = 'topic_comment.id desc';
        /* 分页查询 */
        $count = M($name)
            ->alias('topic_comment')
            ->where($where)
            ->join('__TOPIC__ topic ON topic_comment.topic_id = topic.id')
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->alias('topic_comment')
            ->field($field)
            ->where($where)
            ->join('__TOPIC__ topic ON topic_comment.topic_id = topic.id')
            ->join('LEFT JOIN __USER__ user ON topic_comment.user_id = user.id')
            ->join('LEFT JOIN __USER__ response_user ON topic_comment.response_user_id = response_user.id')
            ->order($order)
            ->limit($limit)
            ->select();

        foreach($list as $k => $v){
            $list[$k]['content'] = urldecode($v['content']);
            $list[$k]['topic_content'] = urldecode($v['topic_content']);
            $list[$k]['user_nick_name'] = urldecode($v['user_nick_name']);
        }

        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 修改模板 edit
     */
    public function edit() {
        if (IS_POST) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $id = I('post.itemID');
            /* 查询条件 */
            $field = 'topic_comment.id,topic_comment.content,topic_comment.create_time,
                      topic.id as topic_id,topic.content as topic_content,topic.upfile,
                      user.id as user_id,user.nick_name as user_nick_name,
                      response_user.id as user_response_id,response_user.nick_name as user_response_nick_name';

            $where['topic_comment.id'] = array('EQ', $id);
            $where['topic_comment.display'] = array('EQ', 1);
            $where['topic.display'] = array('EQ', 1);
            /* 查询列表 */
            $vo = M($name)
                ->alias('topic_comment')
                ->field($field)
                ->where($where)
                ->join('__TOPIC__ topic ON topic_comment.topic_id = topic.id')
                ->join('LEFT JOIN __USER__ user ON topic_comment.user_id = user.id')
                ->join('LEFT JOIN __USER__ response_user ON topic_comment.response_user_id = response_user.id')
                ->find();

            if($vo){
                $vo['content'] = urldecode($vo['content']);
                $vo['topic_content'] = urldecode($vo['topic_content']);
            }

            $this->assign('vo', $vo);
            $this->display();
        }
    }

    /**
     * 设置审核 statusUp
     */
    public function statusUp() {
        $this->do_status();
    }

    /*
     * 设置未审核 statusDown
     */
    public function statusDown() {
        $this->do_status('', false);
    }

    /**
     * 删除 delete
     */
    public function delete() {
        $this->do_delete();
    }

    /**
     * 回调函数
     */
    protected function _after_do_delete() {
        $id = I('post.itemID');
        $field = 'topic.id as topic_id,topic.upfile as topic_upfile,
                  topic_comment.user_id';
        $where['topic_comment.id'] = array('EQ', $id);
        $data = M('TopicComment')
            ->alias('topic_comment')
            ->field($field)
            ->join('left join __TOPIC__ topic ON topic_comment.topic_id = topic.id')
            ->where($where)
            ->find();

        import('Api.ORG.EasemobIMSDK');
        $rest = new \Hxcall();
        $sender = C('EASEMOB.EASEMOB_PREFIX') . '1';
        $receiver = C('EASEMOB.EASEMOB_PREFIX') . $data['user_id'];
        $msg = '你发布的评论审核不通过, 已被删除';
        $ext = array(
            'type'     => 4,
            'id'       => $data['topic_id'],
            'upload'   => $data['topic_upfile'],
            'remarks'  => ''
        );
        $rest->hx_send($sender, $receiver, $msg, $ext);
    }
}