<?php
namespace Home\Model;

use Think\Model;

class ShopApplyModel extends Model {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        array('title', 'require', '请填写商家名称', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('telephone', 'require', '请填写商家电话', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT)
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /* 新增和编辑数据的时候允许写入字段 */

    /* 数据操作 */
    /**
     * 添加评论 do_add
     */
    public function do_add() {
        if ($this->create()) {
            $result = $this->add();
            return $result;
        }
        return false;
    }

    /* 自动验证和自动完成函数 */
}