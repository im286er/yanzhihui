<?php
namespace Manager\Controller;

use Think\Controller;

class ShopAccountController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $getTitle = I('get.title');
        /* 查询列表 */
        if ($getTitle) {
            $where['username'] = array('LIKE', '%' . $getTitle . '%');
            $options['where'] = $where;
        }
        $list = $this->do_list('', $options);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加保存 insert
     */
    public function insert() {
        $this->do_save();
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