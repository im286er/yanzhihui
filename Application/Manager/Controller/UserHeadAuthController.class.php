<?php
namespace Manager\Controller;

class UserHeadAuthController extends BaseController {
    /**
     * 列表 index
     */
    public function index() {
        /* 定义变量 */
        $name = 'User';
        $getTitle = I('get.title');
        /* 搜索条件 */
        if ($getTitle) {
            $where['nick_name'] = array('LIKE', '%' . $getTitle . '%');
        }
        $where['upfile_head_auth_type'] = array('EQ', 2);
        $options['where'] = $where;
        /* 查询列表 */
        $list = $this->do_list($name, $options);
        $this->assign('list', $list);
        $this->display();
    }

    /**
     * 查看 edit
     */
    public function edit() {
        if (IS_POST) {
            /* 定义变量 */
            $name = 'User';
            $id = I('post.itemID');
            $model = M($name);
            $where['id'] = array('EQ', $id);
            $where['upfile_head_auth_type'] = array('EQ', 2);
            $where['display'] = array('EQ', 1);
            $vo = $model->where($where)->find();
            if ($vo) {
                if ($vo['upfile_head'] && !strstr($vo['upfile_head'], 'http://')) {
                    $vo['upfile_head'] = '/Uploads/Images/User/' . $vo['upfile_head'];
                }
                $this->assign('vo', $vo);
            }
            $this->display();
        }
    }

    /**
     * 修改保存 update
     */
    public function update() {
        $name = 'User';
        $model = D($name);
        $result = $model->do_update_head_auth();
        /* 返回信息 */
        if ($result) {
            if($_POST['upfile_head_auth_type'] == 1){
                /* 发送IM 信息 */
                import('Api.ORG.EasemobIMSDK');
                $rest = new \Hxcall();
                $sender = C('EASEMOB.EASEMOB_PREFIX') . '1';
                $receiver = C('EASEMOB.EASEMOB_PREFIX') . $_POST['id'];
                $msg = L('TS_user_authentication_upfile_head');
                $ext = array(
                    'type'     => 5
                );
                $rest->hx_send($sender, $receiver, $msg, $ext);
            }

            $this->ajaxReturn(array('msg' => L('YZ_operation_success'), 'result' => 1, 'href' => U('index')));
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
