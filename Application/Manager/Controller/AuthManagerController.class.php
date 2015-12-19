<?php
namespace Manager\Controller;

class AuthManagerController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 查询列表 */
        $name = 'AuthGroup';
        $list = $this->do_list($name);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 添加保存 insert
     */
    public function insert() {
        $name = 'AuthGroup';
        $this->do_save($name);
    }

    /**
     * 修改模板 edit
     */
    public function edit() {
        if (IS_POST) {
            $name = 'AuthGroup';
            $vo = $this->do_edit($name);
            if ($vo) {
                $this->assign('vo', $vo);
                $this->display();
            }
        }
    }

    /**
     * 修改保存 update
     */
    public function update() {
        $name = 'AuthGroup';
        $this->do_save($name);
    }

    /**
     * 设置审核 statusUp
     */
    public function statusUp() {
        $name = 'AuthGroup';
        $this->do_status($name);
    }

    /*
     * 设置未审核 statusDown
     */
    public function statusDown() {
        $name = 'AuthGroup';
        $this->do_status($name, false);
    }

    /**
     * 删除 delete
     */
    public function delete() {
        $name = 'AuthGroup';
        $this->do_delete($name);
    }

    /*
     * 访问授权页面 access
     */
    public function access($id = NULL) {
        /* 定义变量 */
        $nameAuthRule = 'AuthRule';
        $nameAuthGroup = 'AuthGroup';

        if ($id) {
            $field = 'rule.id,rule.title,menu.title as menu_title';
            $where['rule.display'] = array('EQ', 1);
            $where['menu.display'] = array('EQ', 1);
            $listRuleMenu = M($nameAuthRule)
                ->alias('rule')
                ->field($field)
                ->where($where)
                ->join('__MENU__ menu ON rule.menu_id = menu.id')
                ->select();
            $list = array();
            foreach ($listRuleMenu as $k => $v) {
                $list[$v['menu_title']][] = $v;
            }
            /* 判断选中 */
            $checked = M($nameAuthGroup)->getFieldById($id, 'rules');
            $this->assign('checked', $checked);
            $this->assign('list', $list);
        }
        $this->display();
    }

    /*
     * 修改权限 accessSave
     */
    public function accessSave($id = NULL) {
        if (IS_POST && IS_AJAX) {
            /* 定义变量 */
            $result = D('AuthGroup')->do_accessUpdate($id);
            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1, 'href' => U('access?id=' . $id)));
            } else {
                /* 数据库操作错误 */
                $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1, 'href' => U('access?id=' . $id)));
            }
        }
    }

    /*
     * 访问管理用户 member
     */
    public function member($id = NULL) {
        if ($id) {
            /* 查询数据 */
            $whereMember['id'] = array('NEQ', C('MANAGER_ADMINISTRATOR'));
            $whereMember['display'] = array('EQ', 1);
            $list = M('Member')->field('id,username')->where($whereMember)->select();
            /* 判断选中 */
            $whereChecked['group_id'] = array('EQ', $id);
            $checked = M('AuthGroupAccess')->where($whereChecked)->getField('user_id', true);
            $checked = implode(',', $checked);
            $this->assign('checked', $checked);
            $this->assign('list', $list);
        }
        $this->display();
    }

    /*
     * 修改管理用户 memberSave
     */
    public function memberSave($id = NULL) {
        if (IS_POST && IS_AJAX) {
            /* 定义变量 */
            $result = D('AuthGroupAccess')->do_memberUpdate($id);
            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1, 'href' => U('member?id=' . $id)));
            } else {
                /* 数据库操作错误 */
                $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1, 'href' => U('member?id=' . $id)));
            }
        }
    }
}
