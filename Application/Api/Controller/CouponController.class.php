<?php
namespace Api\Controller;

class CouponController extends BaseController {
    /**
     * 优惠劵购买 buy
     */
    public function buy($user_id = NULL, $shop_id = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D('PayOrder');
            $result = $model->do_add();
            /* 返回信息 */
            if ($result) {
                header('Content-Type:application/json; charset=utf-8');
                echo json_encode(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));

                /* 插入想去 */
                $result_shop_user_went = D('ShopUserWent')->do_add_buy($shop_id, $user_id, $result);
                if ($result_shop_user_went) {
                    /* 关注的人收到通知 */

                    // 获取关注你的人
                    $where_attention['user_attention.to_user_id'] = array('EQ', $user_id);
                    $where_attention['user.push_id'] = array('NEQ', '');
                    $where_attention['user.status'] = array('EQ', 1);
                    $where_attention['user.display'] = array('EQ', 1);
                    $where_attention['user.trace_notify'] = array('EQ', 1);
                    $attention_user_id = M('UserAttention')
                        ->alias('user_attention')
                        ->field('user.push_id')
                        ->where($where_attention)
                        ->join('__USER__ user on user_attention.user_id = user.id')
                        ->select();

                    // 获取去向资料
                    $data_shop_user_went = get_shop_user_went($user_id, $shop_id);

                    if ($data_shop_user_went) {
                        foreach ($attention_user_id as $k => $v) {
                            $push_id[] = $v['push_id'];
                        }
                        $push_id = array_filter(array_unique(array_slice($push_id, 0, 1000)));
                        $this->push_one($push_id, $data_shop_user_went);
                    }

                    exit;
                }
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }
}