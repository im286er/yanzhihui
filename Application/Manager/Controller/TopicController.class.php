<?php
namespace Manager\Controller;

use Think\Controller;

class TopicController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = CONTROLLER_NAME;
        $getTitle = I('get.title');
        $getNickName = I('get.nick_name');
        $getStartTime = I('get.startTime');
        $getEndTime = I('get.endTime');
        /* 查询条件 */
        $field = 'topic.id,topic.upfile,topic.content,topic.province,topic.city,topic.like_count,topic.comment_count,topic.status,topic.create_time,
                  user.nick_name as user_nick_name';
        $where['topic.display'] = array('EQ', 1);
        /* 搜索条件 */
        if ($getTitle) {
            $where['topic.content'] = array('LIKE', '%' . $getTitle . '%');
        }
        if ($getStartTime) {
            $where['topic.create_time'] = array('EGT', strtotime($getStartTime));
            if ($getStartTime && $getEndTime) {
                $where['topic.create_time'] = array('BETWEEN', array(strtotime($getStartTime), strtotime(date('Y-m-d', strtotime($getEndTime . '+1 day')))));
            }
        }
        if($getNickName){
            $where['user.nick_name'] = array('LIKE', '%' . $getNickName . '%');
        }

        /* 查询排序 */
        $order = 'topic.id desc';
        /* 分页查询 */
        $count = M($name)
            ->alias('topic')
            ->where($where)
            ->join('LEFT JOIN __USER__ user ON topic.user_id = user.id')
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->alias('topic')
            ->field($field)
            ->where($where)
            ->join('LEFT JOIN __USER__ user ON topic.user_id = user.id')
            ->order($order)
            ->limit($limit)
            ->select();

        foreach($list as $k => $v){
            $list[$k]['content'] = urldecode($v['content']);
            $list[$k]['user_nick_name'] = urldecode($v['user_nick_name']);
        }

        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 查看 edit
     */
    public function edit() {
        if (IS_POST) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $id = I('post.itemID');
            /* 查询数据 */
            $field = 'topic.id as topic_id,topic.upfile,topic.content,topic.province,topic.city,topic.like_count,topic.comment_count,topic.create_time,
                      user.id as user_id,user.nick_name as user_nick_name';
            $where['topic.id'] = array('EQ', $id);
            $where['topic.display'] = array('EQ', 1);
            $vo = M($name)
                ->alias('topic')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __USER__ user ON topic.user_id = user.id')
                ->find();

            if($vo['content']){
                $vo['content'] = urldecode($vo['content']);
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
        $field = 'id,upfile,user_id';
        $where['id'] = array('EQ', $id);
        $data = M('Topic')->field($field)->where($where)->find();

        /* 发送IM 信息 */
        import('Api.ORG.EasemobIMSDK');
        $rest = new \Hxcall();
        $sender = C('EASEMOB.EASEMOB_PREFIX') . '1';
        $receiver = C('EASEMOB.EASEMOB_PREFIX') . $data['user_id'];
        $msg = L('TS_topic_not_pass');
        $ext = array(
            'type'     => 4,
            'id'       => $data['id'],
            'upload'   => $data['upfile'],
            'remarks'  => ''
        );
        $rest->hx_send($sender, $receiver, $msg, $ext);
    }
}