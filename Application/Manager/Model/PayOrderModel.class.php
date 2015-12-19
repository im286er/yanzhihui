<?php
namespace Manager\Model;
class PayOrderModel extends CommonModel {
    /* 关闭字段自动检测 */
    protected $autoCheckFields = false;

    /* 字段映射 */
    protected $_map = array();

    /* 插入模型数据 操作状态 */

    /* 自动验证规则 */
    protected $_validate = array();

    /* 模型自动完成 */

    /* 新增和编辑数据的时候允许写入字段 */

    /* 数据操作 */

    /* 回滚优惠劵 */
    public function do_recall(){
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                if ($this->create()) {
                    /* 定义变量 */
                    $id = I('post.itemID');

                    /* 数据操作 */
                    $this->startTrans(); //开启事务

                    $whereRecall['id'] = array('EQ', $id);
                    $whereRecall['trade_state'] = array('EQ', 0);
                    $whereRecall['display'] = array('EQ', 1);

                    /* 获取优惠劵信息  */
                    $orderInfo = $this->field('user_id,shop_coupon_like_consume_count')->where($whereRecall)->find();
                    $user_id = $orderInfo['user_id'];
                    $shop_coupon_like_consume_count = $orderInfo['shop_coupon_like_consume_count'];

                    /* 改变优惠劵状态 */
                    $dataRecall['trade_state'] = 2;
                    $dataRecall['update_time'] = NOW_TIME;
                    $resultRecall = $this->where($whereRecall)->save($dataRecall);

                    /* 更改用户积分 */
                    $whereUserCountLike['id'] = array('EQ', $user_id);
                    $whereUserCountLike['status'] = array('EQ', 1);
                    $whereUserCountLike['display'] = array('EQ', 1);
                    $userData = M('User')->field('like_now_count,like_consume_count')->where($whereUserCountLike)->find();

                    /* 修改用户颜值 */
                    $dataUserCountLike['like_now_count'] = $userData['like_now_count'] + $shop_coupon_like_consume_count;
                    $dataUserCountLike['like_consume_count'] = $userData['like_consume_count'] - $shop_coupon_like_consume_count;
                    $resultUserCountLike = M('User')->where($whereUserCountLike)->save($dataUserCountLike);

                    if ($resultRecall && $resultUserCountLike) {
                        $this->commit();//提交事务
                        return true;
                    } else {
                        $this->rollback(); //事务回滚
                    }
                }
            } catch (\Exception $e) {
                $remark = $e->getMessage();
                /* 记录操作异常日志 */
                logs_system_error($remark);
            }
        }
        return $result;
    }

    /* 自动验证和自动完成函数 */
}