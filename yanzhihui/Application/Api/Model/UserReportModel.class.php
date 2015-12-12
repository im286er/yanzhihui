<?php
namespace Api\Model;

class UserReportModel extends CommonModel {
    /* 插入模型数据 操作状态 */

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('report_user_id', 'validate_reportUserId_check', '{%YZ_reportUserId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT)
    );
    /* 模型自动完成 */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /**
     * 举报用户 do_add
     */
    public function do_add() {
        if ($this->create()) {
            $result = $this->add();
            return $result;
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
    /* 验证举报用户ID validate_reportUserId_check */
    protected function validate_reportUserId_check($data) {
        if ($data && I('post.user_id') !== $data) {
            $where['id'] = array('EQ', $data);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('User')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }
}