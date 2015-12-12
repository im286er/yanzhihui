<?php
namespace Manager\Controller;

use Think\Controller;

class UserReportController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = CONTROLLER_NAME;
        /* 查询条件 */
        $field = 'user_report.id,user_report.status,user_report.create_time,
                  user.nick_name as user_nick_name,
                  to_user.nick_name as to_user_nick_name';
        $where['user_report.display'] = array('EQ', 1);
        /* 查询排序 */
        $order = 'user_report.id desc';
        /* 分页查询 */
        $count = M($name)
            ->alias('user_report')
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->alias('user_report')
            ->field($field)
            ->where($where)
            ->join('LEFT JOIN __USER__ user ON user_report.user_id = user.id')
            ->join('LEFT JOIN __USER__ to_user ON user_report.report_user_id = to_user.id')
            ->order($order)
            ->limit($limit)
            ->select();
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
            $field = 'user_report.id,user_report.report_user_id,user_report.user_id,user_report.create_time,
                      user.nick_name as user_nick_name,
                      to_user.nick_name as to_user_nick_name';
            $where['user_report.id'] = array('EQ', $id);
            $where['user_report.display'] = array('EQ', 1);
            $vo = M($name)
                ->alias('user_report')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __USER__ user ON user_report.user_id = user.id')
                ->join('LEFT JOIN __USER__ to_user ON user_report.report_user_id = to_user.id')
                ->find();
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