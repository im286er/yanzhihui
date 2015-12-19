<?php
namespace Manager\Model;
class UserModel extends CommonModel {
    /* 插入模型数据 操作状态 */
    const MODEL_HEAD_AUTH = 4;

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        array('upfile_head_auth_type', array(1, 3), '{%YZ_shopLikeType_error}', self::MUST_VALIDATE, 'in', self::MODEL_HEAD_AUTH)
    );

    /* 模型自动完成 */
    protected $_auto = array();

    /* 数据操作 */
    public function do_update_head_auth() {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                if ($this->create('', self::MODEL_HEAD_AUTH)) {
                    $where['id'] = array('EQ', I('post.id'));
                    $where['upfile_head_auth_type'] = array('EQ', 2);
                    $result = $this->where($where)->setField('upfile_head_auth_type', I('upfile_head_auth_type'));
                }
            } catch (\Exception $e) {
                $remark = $e->getMessage();
                /* 记录操作异常日志 */
                logs_system_error($remark);
            }
        }
        return $result;
    }
}