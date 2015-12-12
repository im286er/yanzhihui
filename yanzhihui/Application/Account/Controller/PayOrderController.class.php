<?php
namespace Account\Controller;

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
            $where['pay_order.shop_account_id'] = array('EQ', UID);
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
            $where['pay_order.shop_account_id'] = array('EQ', UID);
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
    public function recall() {
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

    /**
     * 验证优惠劵
     */
    public function consume() {
        $this->display();
    }

    public function do_consume() {
        $name = CONTROLLER_NAME;
        $model = D($name);
        $result = $model->do_consume();

        /* 返回信息 */
        if ($result) {
            /* ShopUserWent 添加去向 */
            $where['trade_no'] = array('EQ', I('post.verify'));
            $where['trade_state'] = array('EQ', 1);
            $where['shop_account_id'] = array('EQ', UID);
            $where['display'] = array('EQ', 1);
            $shopUserWent = $model->field('id,user_id,shop_id,shop_coupon_info')->where($where)->find();
            $shopUserWentInfo = json_decode($shopUserWent['shop_coupon_info'], true);

            $content = '使用了"' . $shopUserWentInfo['title'] . '"';
            $dataUserWent['shop_id'] = $shopUserWent['shop_id'];
            $dataUserWent['user_id'] = $shopUserWent['user_id'];
            $dataUserWent['content'] = $content;
            $dataUserWent['pay_order_id'] = $shopUserWent['id'];
            $dataUserWent['create_time'] = NOW_TIME;
            M('ShopUserWent')->add($dataUserWent);

            /* 关注的人收到通知 */
            // 获取关注你的人
            $where_attention['user_attention.to_user_id'] = array('EQ', $shopUserWent['user_id']);
            $where_attention['user.push_id'] = array('NEQ', '');
            $where_attention['user.status'] = array('EQ', 1);
            $where_attention['user.display'] = array('EQ', 1);
            $attention_user_id = M('UserAttention')
                ->alias('user_attention')
                ->field('user.push_id')
                ->where($where_attention)
                ->join('__USER__ user on user_attention.user_id = user.id')
                ->select();

            // 获取去向资料
            $data_shop_user_went = get_shop_user_went($shopUserWent['user_id'], $shopUserWent['shop_id']);
            if($data_shop_user_went) {
                foreach($attention_user_id as $k => $v){
                    $push_id[] = $v['push_id'];
                }
                $push_id = array_filter(array_unique(array_slice($push_id, 0, 1000)));
                R('Api/Api/push_one', array($push_id, $data_shop_user_went));
            }

            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1, 'href' => U('consume')));
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
}