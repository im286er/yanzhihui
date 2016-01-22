<?php
namespace Manager\Controller;

use Think\Controller;

class BaseController extends Controller {
    public function _initialize() {
        /* 获取用户ID */
        define('UID', is_login());

        /* 判断是否登录 */
        if (!UID) {
            $this->redirect('Public/login');
        }

        /* 判断是否为超级管理员 */
        define('IS_ROOT', is_administrator());

        /* 检测访问权限 */
        $access = $this->accessControl();
        if ($access === false) {
            R('Empty/index');
        } elseif ($access === NULL) {
            /* 检测分类栏目有关的各项动态权限 */
            $dynamic = $this->checkDynamic();
            if ($dynamic === NULL) {
                /* 检测非动态权限 */
                $rule = strtolower(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);
                if (!$this->checkRule($rule)) {
                    R('Empty/index');
                }
            } elseif ($dynamic === false) {
                R('Empty/index');
            }
        }
    }

    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务 accessControl
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl() {
        /* 管理员允许访问任何页面 */
        if (IS_ROOT) {
            return true;
        }
        $deny = C('AUTH_CONFIG.AUTH_DENY_VISIT');   //非超管禁止访问的模块
        $allow = C('AUTH_CONFIG.AUTH_ALLOW_VISIT');  //非超管可直接访问的模块
        $allowAction = C('AUTH_CONFIG.AUTH_ALLOW_ACTION'); //非超管可直接访问的节点
        $check = strtolower(CONTROLLER_NAME . '/' . ACTION_NAME);
        if (!empty($deny) && in_array_case($check, $deny)) {
            return false;
        }
        if (!empty($allow) && in_array_case($check, $allow)) {
            return true;
        }
        if (!empty($allowAction) && in_array_case(ACTION_NAME, $allowAction)) {
            return true;
        }
        /* 需要检测节点权限 */
        return NULL;
    }

    /**
     * 检测是否是需要动态判断的权限 checkDynamic
     * @return boolean|null
     *   返回true则表示当前访问有权限
     *   返回false则表示当前访问无权限
     *   返回null，则会进入checkRule根据节点授权判断权限
     */
    protected function checkDynamic() {
        /* 管理员允许访问任何页面 */
        if (IS_ROOT) {
            return true;
        }
        /* 不明,需checkRule */
        return NULL;
    }

    /**
     * 权限检测 checkRule
     * @param string $rule 检测的规则
     * @param string $mode check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type = 1, $mode = 'url') {
        if (IS_ROOT) {
            return true;//管理员允许访问任何页面
        }
        static $Auth = NULL;
        if (!$Auth) {
            $Auth = new \Manager\ORG\Auth();
        }
        if (!$Auth->check($rule, UID, $type, $mode)) {
            return false;
        }
        return true;
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
            $count = M($name)->where($where)->group($options['group'])->count();
            $limit = $this->Page($count);
        }
        if (empty($options['order'])) {
            $options['order'] = 'id desc';
        }
        $list = $model->field($options['field'][0], $options['field'][1])->where($where)->order($options['order'])->group($options['group'])->limit($limit)->select();
        return $list;
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
     * 默认回调函数
     */
    protected function _after_do_insert() {
    }

    protected function _after_do_update() {
    }

    protected function _after_do_statusUp() {
    }

    protected function _after_do_statusDown() {
    }

    protected function _after_do_delete() {
    }

    /**
     * 404错误页
     */
    public function _empty() {
        R('Empty/index');
    }


	 /**
     * 设置置顶 do_top_
     * @param null $name
     * @param bool $statusUp
     */
    protected function do_top_($name = NULL, $statusUp = 1, $options = array()) {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = D($name);
        $result = $model->do_top_($options, $statusUp);
        /* 返回信息 */
        if ($result) {
            /* 成功回调执行函数 */
          
            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1));
        }
        $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
    }


	/**
     * 设置置顶 do_autodown
     * @param null $name
     * @param bool $statusUp
     */
    protected function do_autodown($name = NULL, $statusUp = 1, $options = array()) {
        if (!$name) {
            $name = CONTROLLER_NAME;
        }
        $model = D($name);
        $result = $model->do_autodown($options, $statusUp);
        /* 返回信息 */
        if ($result) {
            /* 成功回调执行函数 */
          
            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1));
        }
        $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
    }
}