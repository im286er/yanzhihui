<?php
namespace Manager\Controller;
class SettingController extends BaseController {
    /**
     * 修改模板 edit
     */
    public function edit() {
        $where['id'] = array('EQ', 1);
        $vo = $this->do_edit('', $where);
        if ($vo) {
            $this->assign('vo', $vo);
            $this->display();
        }
    }

    /**
     * 修改保存 update
     */
    public function update() {
        $postId = I('post.id');
        if ($postId > 0) {
            $condition['id'] = array('EQ', 1);
            $data_array['condition'] = $condition;
            $this->do_save('', $data_array, 'edit');
        }
    }

    /**
     * 回调函数
     */
    protected function _after_do_update() {
        $data = I('post.');
        S('data_setting', $data);
    }
}