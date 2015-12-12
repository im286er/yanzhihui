<?php
namespace Account\Controller;

use Think\Controller;

class BaseController extends Controller {
    public function _initialize() {
        /* 获取用户ID */
        define('UID', is_login());

        /* 判断是否登录 */
        if (!UID) {
            $this->redirect('Public/login');
        }
    }

    /**
     * 新增模板 add
     */
    public function add() {
        $vo = NULL;
        $this->assign('vo', $vo);
        $this->display('edit');
    }

    /**
     * 修改模板 edit
     */
    public function edit() {
        if (IS_POST) {
            $name = CONTROLLER_NAME;
            $vo = $this->do_edit($name);
            if ($vo) {
                $this->assign('vo', $vo);
                $this->display();
            }
        }
    }

    /**
     * 修改模板 do_edit
     * @param null $name
     * @param array $options
     * @return mixed
     */
    protected function do_edit($name = NULL, $options = array()) {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = M($name);
        $id = I('post.itemID');
        $where['id'] = array('EQ', $id);
        $where['display'] = array('EQ', 1);
        $where = array_merge($where, $options);
        $result = $model->where($where)->find();
        return $result;
    }

    /**
     * 404错误页
     */
    public function _empty() {
        R('Empty/index');
    }

    /**
     * 查询列表 do_list
     * @param $name
     * @param array $options (field,where,order)
     * @param bool $pageshow
     * @return bool
     */
    protected function do_list($name = NULL, $options = array(), $pageshow = true) {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = M($name);
        /* 查询条件 */
        $where['display'] = array('EQ', 1);
        if ($options['where']) {
            $where = array_merge($options['where'], $where);
        }
        /* 分页 */
        $limit = NULL;
        if ($pageshow) {
            $count = $model->where($where)->group($options['group'])->count();
            $limit = $this->Page($count);
        }
        if (empty($options['order'])) {
            $options['order'] = 'id desc';
        }
        $list = $model->field($options['field'][0], $options['field'][1])->where($where)->order($options['order'])->group($options['group'])->limit($limit)->select();
        return $list;
    }

    /**
     * 加载分页类
     * @param $count
     * @return string
     */
    protected function Page($count) {
        $Page = new \Manager\ORG\Page($count, C('PAGE_NUM'));
        $Page->rollPage = C('ROLL_PAGE');
        $Page->setConfig('theme', '%first%%upPage%%linkPage%%downPage%%end%%totalPage%');
        $this->assign('page', $Page->show());
        $limit = $Page->firstRow . ',' . $Page->listRows;
        return $limit;
    }

    /**
     * 保存提交 do_save
     * @param null $name
     * @param array $data_array
     * @param string $href
     */
    protected function do_save($name = NULL, $data_array = array(), $href = 'index') {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = D($name);
        $postId = I('post.id');
        if ($postId > 0) {
            $result = $model->do_update($data_array['condition'], $data_array['data_array']);
        } else {
            $result = $model->do_insert($data_array['data_array']);
        }
        /* 返回信息 */
        if ($result) {
            /* 成功回调执行函数 */
            if ($postId > 0) {
                $this->_after_do_update();
            } else {
                $this->_after_do_insert();
            }
            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1, 'href' => U($href)));
        } else {
            $result = $model->getError();
            if (is_array($result) && count($result)) {
                /* 验证错误 */
                $errorMsg = validate_error($result);
                $this->ajaxReturn(array('formError' => $errorMsg, 'result' => -1));
            }
            /* 数据库操作错误 */
            $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
        }
    }

    protected function _after_do_update() {
    }

    /**
     * 默认回调函数
     */
    protected function _after_do_insert() {
    }

    /**
     * 审核 do_status
     * @param null $name
     * @param bool $statusUp
     */
    protected function do_status($name = NULL, $statusUp = true, $options = array()) {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = D($name);
        $result = $model->do_status($options, $statusUp);
        /* 返回信息 */
        if ($result) {
            /* 成功回调执行函数 */
            if ($statusUp) {
                $this->_after_do_statusUp();
            } else {
                $this->_after_do_statusDown();
            }
            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1));
        }
        $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
    }

    protected function _after_do_statusUp() {
    }

    protected function _after_do_statusDown() {
    }

    /**
     * 删除 do_delete
     * @param null $name
     * @param array $data_array
     */
    protected function do_delete($name = NULL, $data_array = array()) {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = D($name);
        if (!$data_array['condition']) {
            $data_array['condition'] = array();
        }
        $result = $model->do_delete($data_array['condition'], $data_array['type']);
        /* 返回信息 */
        if ($result) {
            /* 成功回调执行函数 */
            $this->_after_do_delete();
            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1));
        } else {
            $errorMsg = $model->getError();
            if ($errorMsg) {
                /* 验证错误 */
                $this->ajaxReturn(array('msg' => $errorMsg, 'result' => 1));
            }
            $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
        }
    }

    protected function _after_do_delete() {
    }
}