<?php
namespace Manager\Controller;

class IndexController extends BaseController {
    public function index() {
        $menus = $this->get_menus();
        $this->assign('menus', $menus);
        $this->display();
    }

    public function home() {
        $this->display();
    }

    /* 读取菜单 */
    private function get_menus() {
        /* 查询缓存 */
        if (!S('managerAllMenu')) {
            /* 读取全部菜单 */
            $whereAllMenu['display'] = array('EQ', 1);
            $managerAllMenu = M('Menu')->where($whereAllMenu)->select();
            S('managerAllMenu', $managerAllMenu, 86400); //缓存一天
        }
        /* 定义变量 */
        $managerRule = session('auth_rules');
        $managerAllMenu = S('managerAllMenu');
        $menuTop = array();
        $menuChild = array();
        $menuChildId = '';
        $menuChildTitle = '';
        $menuTopId = '';
        /* 查询用户权限 */
        if (IS_ROOT) {
            foreach ($managerAllMenu as $k => $v) {
                if ($v['level'] == 3) {
                    $managerRule[] = $v['id'];
                }
            }
        }
        $managerRule = array_unique($managerRule);
        /* 遍历二三级菜单 */
        foreach ($managerAllMenu as $value) {
            if (in_array($value['id'], $managerRule)) {
                foreach ($managerAllMenu as $v) {
                    if ($value['pid'] == $v['id']) {
                        $menuChildId = $v['pid'];
                        $menuChildTitle = $v['title'];
                        $menuTopId[] = $v['pid'];
                    }
                }
                $menuChild[$value['pid']]['pid'] = $menuChildId;
                $menuChild[$value['pid']]['title'] = $menuChildTitle;
                $menuChild[$value['pid']]['child'][] = $value;
            }
        }
        /* 读取顶级菜单 */
        $menuTopId = array_unique($menuTopId);
        foreach ($managerAllMenu as $v) {
            if (in_array($v['id'], $menuTopId)) {
                $menuTop[] = array('id' => $v['id'], 'title' => $v['title']);
            }
        }
        /* 合并数组 */
        $list['top'] = $menuTop;
        $list['child'] = $menuChild;
        return $list;
    }
}