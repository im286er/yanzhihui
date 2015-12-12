<?php
namespace Manager\Controller;

class ShopCouponController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        if (IS_GET) {
            $name = CONTROLLER_NAME;
            $getTitle = I('get.title');
            $model = M($name);
            /* 查询条件 */
            $field = 'shop_coupon.id,shop_coupon.title,shop_coupon.coupon_type,shop_coupon.coupon_worth,shop_coupon.like_consume_count,shop_coupon.status,shop_coupon.create_time,
                      shop.title as shop_title';
            $where['shop_coupon.display'] = array('EQ', 1);
            $where['shop.display'] = array('EQ', 1);
            /* 搜索条件 */
            if ($getTitle) {
                $where['shop_coupon.title'] = array('LIKE', '%' . $getTitle . '%');
            }
            $order = 'shop_coupon.id desc';
            /* 分页 */
            $limit = NULL;
            $count = $model
                ->alias('shop_coupon')
                ->where($where)
                ->join('__SHOP__ shop ON shop_coupon.shop_id = shop.id')
                ->count();
            $limit = $this->Page($count);
            /* 读取数据 */
            $list = $model
                ->alias('shop_coupon')
                ->field($field)
                ->where($where)
                ->join('__SHOP__ shop ON shop_coupon.shop_id = shop.id')
                ->order($order)
                ->limit($limit)
                ->select();
            $this->assign('list', $list);
            $this->display();
        }
    }

    /**
     * 新增模板 add
     */
    public function add() {
        $vo = NULL;
        /* 获取门店 */
        $whereShop['status'] = array('EQ', 1);
        $whereShop['display'] = array('EQ', 1);
        $vo['shop'] = M('Shop')->field('id,title')->where($whereShop)->order('id asc')->select();

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
            $vo = $this->do_edit();
            if ($vo) {
                /* 获取门店 */
                $whereShop['status'] = array('EQ', 1);
                $whereShop['display'] = array('EQ', 1);
                $vo['shop'] = M('Shop')->field('id,title')->where($whereShop)->order('id asc')->select();

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