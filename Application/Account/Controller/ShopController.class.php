<?php
namespace Account\Controller;

class ShopController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        if (IS_GET) {
            /* 查询列表 */
            $where['shop_account_id'] = array('EQ', UID);
            $options['where'] = $where;
            $list = $this->do_list('', $options);
            $this->assign('list', $list);
            $this->display();
        }
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
            $where['shop_account_id'] = array('EQ', UID);
            $vo = $this->do_edit($name, $where);
            if ($vo) {
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
        $postId = I('post.id');
        $where['id'] = array('EQ', $postId);
        $where['shop_account_id'] = array('EQ', UID);

        $data_array['condition'] = $where;
        $this->do_save('', $data_array);
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
}