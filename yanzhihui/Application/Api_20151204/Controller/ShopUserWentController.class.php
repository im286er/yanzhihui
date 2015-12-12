<?php
namespace Api\Controller;

class ShopUserWentController extends BaseController {
    /**
     * 去向列表 - 商家详情 index
     */
    public function index($get_user_id = NULL, $shop_id = NULL) {
        if (IS_GET && $get_user_id || $shop_id) {
            /* 定义变量 */
            $name = CONTROLLER_NAME;
            $jsonInfo = D($name)->do_index();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    /**
     * 去向通知列表 index_notice
     */
    public function index_notice($user_id = NULL) {
        if (IS_GET && $user_id) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $jsonInfo = $model->do_index_notice($user_id);
            /* 更新用户读取去向时间 */
            $model->do_read();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $jsonInfo));
        }
    }

    /**
     * 用户去向 add
     */
    public function add($user_id = NULL, $shop_id = NULL) {
        if (IS_POST) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            $result = $model->do_add();
            /* 返回信息 */
            if ($result) {
                /* 关注的人收到通知 */
                // 获取关注你的人
                $where_attention['user_attention.to_user_id'] = array('EQ', $user_id);
                $where_attention['user.push_id'] = array('NEQ', '');
                $where_attention['user.status'] = array('EQ', 1);
                $where_attention['user.display'] = array('EQ', 1);
                $attention_user_id = M('UserAttention')
                    ->alias('user_attention')
                    ->field('user.push_id')
                    ->where($where_attention)
                    ->join('__USER__ user on user_attention.user_id = user.id')
                    ->select();

                if($attention_user_id){
                    // 获取去向资料
                    $data_shop_user_went = get_shop_user_went($user_id, $shop_id);

                    if ($data_shop_user_went) {
                        foreach ($attention_user_id as $k => $v) {
                            $push_id[] = $v['push_id'];
                        }
                        $push_id = array_filter(array_unique(array_slice($push_id, 0, 1000)));
                        $this->push_one($push_id, $data_shop_user_went);
                    }
                }

                $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success')));
            } else {
                $this->return_post($model);
            }
        }
        $this->ajaxReturn(array('RESPONSE_STATUS' => 500, 'Tips' => L('YZ_return_failure')));
    }

    /**
     * 未读数量
     */
    public function unread_info() {
        if (IS_GET) {
            /* 定义变量 */
            $model = D(CONTROLLER_NAME);
            /* 读取列表 */
            $json_data = $model->do_unread_info();
            $this->ajaxReturn(array('RESPONSE_STATUS' => 100, 'Tips' => L('YZ_return_success'), 'RESPONSE_INFO' => $json_data));
        }
    }
}