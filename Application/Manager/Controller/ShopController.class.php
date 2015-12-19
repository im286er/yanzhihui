<?php
namespace Manager\Controller;

use Think\Controller;

class ShopController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = CONTROLLER_NAME;
        $getTitle = I('get.title');
        /* 查询条件 */
        $field = 'shop.id,shop.title,shop.province,shop.city,shop.telephone,shop.upfile,shop.status,shop.create_time,
                  shop_account.username';
        $where['shop.display'] = array('EQ', 1);
        $where['shop_account.display'] = array('EQ', 1);
        /* 搜索条件 */
        if ($getTitle) {
            $where['shop.title'] = array('LIKE', '%' . $getTitle . '%');
        }
        /* 查询排序 */
        $order = 'shop.id desc';
        /* 分页查询 */
        $count = M($name)
            ->alias('shop')
            ->where($where)
            ->join('__SHOP_ACCOUNT__ shop_account ON shop.shop_account_id = shop_account.id')
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->alias('shop')
            ->field($field)
            ->where($where)
            ->join('__SHOP_ACCOUNT__ shop_account ON shop.shop_account_id = shop_account.id')
            ->order($order)
            ->limit($limit)
            ->select();
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 新增模板 add
     */
    public function add() {
        $vo = NULL;
        /* 获取商家 */
        $whereShop['status'] = array('EQ', 1);
        $whereShop['display'] = array('EQ', 1);
        $vo['ShopAccount'] = M('ShopAccount')->field('id,username')->where($whereShop)->order('id asc')->select();

        $this->assign('vo', $vo);
        $this->display('edit');
    }

    /**
     * 添加保存 insert
     */
    public function insert() {
        $this->do_save();
    }

    /**
     * 修改模板 edit
     */
    public function edit() {
        if (IS_POST) {
            $name = CONTROLLER_NAME;
            $vo = $this->do_edit($name);
            if ($vo) {
                /* 获取商家 */
                $whereShop['status'] = array('EQ', 1);
                $whereShop['display'] = array('EQ', 1);
                $vo['ShopAccount'] = M('ShopAccount')->field('id,username')->where($whereShop)->order('id asc')->select();
                /* 获取图片集 */
                if ($vo['upfile_list']) {
                    $vo['upfile_list_arr'] = explode(',', $vo['upfile_list']);
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

    /**
     * 回调函数
     */
    protected function _after_do_insert() {
        $name = CONTROLLER_NAME;
        /* 移动图片 */
        $path = 'Images/' . $name;
        $upfile = I('post.upfile');
        $upfile_list = I('post.upfile_list');
        $upfileArr = array_merge(explode(',', $upfile), explode(',', $upfile_list));
        move_upload_file($path, implode(',', $upfileArr));
    }

    protected function _after_do_update() {
        $name = CONTROLLER_NAME;
        /* 移动图片 */
        $path = 'Images/' . $name;
        $upfile = I('post.upfile');
        $upfile_list = I('post.upfile_list');
        $upfileArr = array_merge(explode(',', $upfile), explode(',', $upfile_list));
        move_upload_file($path, implode(',', $upfileArr));
    }

    /**
     * 上传图片 upload
     */
    public function upload() {
        if (IS_POST) {
            $result = upload_file();
            $this->ajaxReturn($result);
        }
    }
}