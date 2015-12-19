<?php
namespace Manager\Controller;

class PayOrderController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        if (IS_GET) {
            $name = CONTROLLER_NAME;
            $model = M($name);
            /* 查询条件 */
            $field = 'pay_order.id,pay_order.order_id,pay_order.trade_no,pay_order.create_time,pay_order.trade_state,pay_order.update_time,
                      shop.title as shop_title,
                      shop_coupon.title as shop_coupon_title';
            $where['pay_order.display'] = array('EQ', 1);
            $order = 'pay_order.id desc';
            /* 分页 */
            $limit = NULL;
            $count = $model
                ->alias('pay_order')
                ->where($where)
                ->count();
            $limit = $this->Page($count);
            /* 读取数据 */
            $list = $model
                ->alias('pay_order')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __SHOP_COUPON__ shop_coupon ON pay_order.shop_coupon_id = shop_coupon.id')
                ->join('LEFT JOIN __SHOP__ shop ON pay_order.shop_id = shop.id')
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
        R('Empty/index');
    }

    /**
     * 查看模板 read
     */
    public function read() {
        if (IS_POST) {
            $name = CONTROLLER_NAME;
            $id = I('post.itemID');
            $model = M($name);
            /* 查询条件 */
            $field = 'pay_order.id,pay_order.order_id,pay_order.trade_no,pay_order.shop_coupon_info,pay_order.create_time,pay_order.trade_state,pay_order.update_time,
                      shop.title as shop_title,
                      shop_coupon.title as shop_coupon_title,
                      user.nick_name';
            $where['pay_order.id'] = array('EQ', $id);
            $where['pay_order.display'] = array('EQ', 1);
            /* 读取数据 */
            $vo = $model
                ->alias('pay_order')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __SHOP_COUPON__ shop_coupon ON pay_order.shop_coupon_id = shop_coupon.id')
                ->join('LEFT JOIN __SHOP__ shop ON pay_order.shop_id = shop.id')
                ->join('LEFT JOIN __USER__ user ON pay_order.user_id = user.id')
                ->find();
            if ($vo) {
                $vo['shop_coupon_info'] = json_decode($vo['shop_coupon_info'], true);
                $this->assign('vo', $vo);
                $this->display();
            }
        }
    }

    /**
     * 回滚优惠劵 recall
     */
    public function recall(){
        if (IS_POST) {
            $name = CONTROLLER_NAME;
            $model = D($name);
            $result = $model->do_recall();

            /* 返回信息 */
            if ($result) {
                $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1));
            }
            $this->ajaxReturn(array('msg' => L('YZ_operation_fail'), 'result' => 1));
        }
    }
}