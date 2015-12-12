<?php
namespace Manager\Controller;

use Think\Controller;

class TopicReportController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = CONTROLLER_NAME;
        $getTitle = I('get.title');
        /* 查询条件 */
        $field = 'topic_report.id,topic_report.content,topic_report.status,topic_report.create_time,
                  topic.upfile,topic.content as topic_content,
                  user.nick_name as user_nick_name';
        $where['topic_report.display'] = array('EQ', 1);
        /* 搜索条件 */
        if ($getTitle) {
            $where['topic_report.content'] = array('LIKE', '%' . $getTitle . '%');
        }
        /* 查询排序 */
        $order = 'topic_report.id desc';
        /* 分页查询 */
        $count = M($name)
            ->alias('topic_report')
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->alias('topic_report')
            ->field($field)
            ->where($where)
            ->join('LEFT JOIN __TOPIC__ topic ON topic_report.topic_id = topic.id')
            ->join('LEFT JOIN __USER__ user ON topic_report.user_id = user.id')
            ->order($order)
            ->limit($limit)
            ->select();

        foreach($list as $k => $v){
            $list[$k]['topic_content'] = urldecode($v['topic_content']);
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
            $field = 'topic_report.id,topic_report.content,topic_report.create_time,
                      topic.id as topic_id,topic.upfile,topic.content as topic_content,
                      user.id as user_id,user.nick_name as user_nick_name';
            $where['topic_report.id'] = array('EQ', $id);
            $where['topic_report.display'] = array('EQ', 1);
            $vo = M($name)
                ->alias('topic_report')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __TOPIC__ topic ON topic_report.topic_id = topic.id')
                ->join('LEFT JOIN __USER__ user ON topic_report.user_id = user.id')
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
}