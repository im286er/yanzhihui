<?php
namespace Manager\Controller;

use Think\Controller;

class ShopApplyController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = CONTROLLER_NAME;
        /* 查询条件 */
        $field = 'shop_apply.id,shop_apply.title,shop_apply.district,shop_apply.telephone,shop_apply.status,shop_apply.create_time,
                  user.nick_name';
        $where['shop_apply.display'] = array('EQ', 1);
        /* 查询排序 */
        $order = 'shop_apply.id desc';
        /* 分页查询 */
        $count = M($name)
            ->alias('shop_apply')
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->alias('shop_apply')
            ->field($field)
            ->where($where)
            ->join('LEFT JOIN __USER__ user ON shop_apply.user_id = user.id')
            ->order($order)
            ->limit($limit)
            ->select();
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 修改模板 edit
     */
    public function edit() {
        if (IS_POST) {
            $id = I('post.itemID');
            $name = CONTROLLER_NAME;
            $field = 'shop_apply.id,shop_apply.title,shop_apply.district,shop_apply.telephone,shop_apply.status,shop_apply.create_time,
                  user.nick_name';
            $where['shop_apply.id'] = array('EQ', $id);
            $where['shop_apply.display'] = array('EQ', 1);
            $vo = M($name)
                ->alias('shop_apply')
                ->field($field)
                ->where($where)
                ->join('LEFT JOIN __USER__ user ON shop_apply.user_id = user.id')
                ->find();
            if ($vo) {
                $this->assign('vo', $vo);
                $this->display();
            }
        }
    }

    /**
     * 设置审核 statusUp
     */
    public function statusUp() {
        $this->do_status();
    }

    /**
     * 删除 delete
     */
    public function delete() {
        $this->do_delete();
    }

    /**
     * 回调函数
     */
    protected function _after_do_statusUp() {
        $id = I('post.itemID');
        $name = CONTROLLER_NAME;
        $where['id'] = array('EQ', $id);
        $where['display'] = array('EQ', 1);
        $data = M($name)->field('title,user_id')->where($where)->find();

        /* 派发奖励 */
        $where_reward['id'] = array('EQ', $data['user_id']);
        $where_reward['status'] = array('EQ', 1);
        $where_reward['display'] = array('EQ', 1);
        $user_reward = M('User')->field('like_count,like_now_count')->where($where_reward)->find();
        $data_reward['like_count'] = $user_reward['like_count'] + 300;
        $data_reward['like_now_count'] = $user_reward['like_now_count'] + 300;
        M('User')->where($where_reward)->save($data_reward);

        /* 发送IM 信息 */
        import('Api.ORG.EasemobIMSDK');
        $rest = new \Hxcall();
        $sender = C('EASEMOB.EASEMOB_PREFIX') . '1';
        $receiver = C('EASEMOB.EASEMOB_PREFIX') . $data['user_id'];
        $msg = '非常感谢您的热心，您推荐的商家“'. $data['title'] .'”已经通过了小颜的审核。300颜币已入账，请笑纳并继续推荐';

        $rest->hx_send($sender, $receiver, $msg, array('type' => 0));
    }
}