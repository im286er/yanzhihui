<?php
namespace Manager\Controller;

use Think\Controller;

class UserController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $getTitle = I('get.title');
        $getStartTime = I('get.startTime');
        $getEndTime = I('get.endTime');
        /* 查询列表 */
        if ($getTitle) {
            $where['nick_name'] = array('LIKE', '%' . $getTitle . '%');
        }
        if ($getStartTime) {
            $where['create_time'] = array('EGT', strtotime($getStartTime));
            if ($getStartTime && $getEndTime) {
                $where['create_time'] = array('BETWEEN', array(strtotime($getStartTime), strtotime(date('Y-m-d', strtotime($getEndTime . '+1 day')))));
            }
        }
        $options['where'] = $where;
        $list = $this->do_list('', $options);

        foreach($list as $k => $v){
            if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                $list[$k]['upfile_head'] = '/Uploads/Images/User/' . $v['upfile_head'];
            }
        }
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 修改模板 edit
     */
    public function edit() {
        if (IS_POST) {
            $name = CONTROLLER_NAME;
            $vo = $this->do_edit($name);
            if ($vo) {
                if ($vo['upfile_head'] && !strstr($vo['upfile_head'], 'http://')) {
                    $vo['upfile_head'] = '/Uploads/Images/User/' . $vo['upfile_head'];
                }
                $this->assign('vo', $vo);
                $this->display();
            }
        }
    }

    /**
     * 修改保存 update
     */
    public function update() {
        $this->do_save();
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