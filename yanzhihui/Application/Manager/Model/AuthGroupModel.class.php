<?php
namespace Manager\Model;
class AuthGroupModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        array('title', 'require', '{%YZ_enter_title}', self::MUST_VALIDATE),
        array('title', 'validate_title_unique', '{%YZ_title_unique_error}', self::MUST_VALIDATE, 'callback'),
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );

    /* 新增和编辑数据的时候允许写入字段 */
    protected $insertFields = 'title';
    protected $updateFields = 'id,title';

    /* 数据操作 */
    /**
     * 修改权限 do_accessUpdate
     */
    public function do_accessUpdate($id = NULL) {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                $rules = I('post.rules');
                $where['id'] = array('EQ', $id);
                $result = $this->where($where)->setField('rules', $rules);
            } catch (\Exception $e) {
                $remark = $e->getMessage();
                /* 记录操作异常日志 */
                logs_system_error($remark);
            }
        }
        return $result;
    }

    /**
     * 删除 do_delete
     * @return bool|int
     */
    public function do_delete() {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            $id = I('post.itemID');
            /* 判断分类下是否有内容 */
            $whereCount['group_id'] = array('EQ', $id);
            $count = M('AuthGroupAccess')->where($whereCount)->count();
            if (empty($count)) {
                /* 捕获异常 */
                try {
                    $where['id'] = array('EQ', $id);
                    $where['display'] = array('EQ', 1);
                    $result = $this->where($where)->setField('display', 0);
                } catch (\Exception $e) {
                    $remark = $e->getMessage();
                    /* 记录操作异常日志 */
                    logs_system_error($remark);
                }
            } else {
                $this->error = L('YZ_auth_group_exist_member');
            }
        }
        return $result;
    }

    /* 自动验证和自动完成函数 */
}