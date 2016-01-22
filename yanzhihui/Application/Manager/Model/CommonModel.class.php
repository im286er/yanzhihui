<?php
namespace Manager\Model;

use Think\Model;

class CommonModel extends Model {
    /* 设置批量验证所有成员属性 */
    protected $patchValidate = true;

    /**
     * 添加 doInsert
     * @param null $data
     * @return int|mixed
     */
    public function do_insert($data_array = NULL) {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                if ($this->create()) {
                    /* 合并追加数组 */
                    if ($data_array && is_array($data_array)) {
                        foreach ($data_array as $k => $v) {
                            $this->$k = $v;
                        }
                    }
                    $result = $this->add();
                }
            } catch (\Exception $e) {
                $remark = $e->getMessage();
                /* 记录操作异常日志 */
                logs_system_error($remark);
            }
        }
        return $result;
    }

    /**
     * 修改 doUpdate
     * @param null $condition
     * @param null $data_array
     * @return bool|int
     */
    public function do_update($condition = NULL, $data_array = NULL) {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                if ($this->create()) {
                    /* 合并追加数组 */
                    if ($data_array && is_array($data_array)) {
                        foreach ($data_array as $k => $v) {
                            $this->$k = $v;
                        }
                    }
                    if (!$condition && in_array($this->getPk(), array_keys(I('post.')))) {
                        $result = $this->save();
                    } else {
                        $result = $this->where($condition)->save();
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

    /**
     * 审核 doStatus
     * @param array $condition
     * @param string $status
     * @return bool|int
     */
    public function do_status($condition = array(), $statusUp) {
        $result = 0;
        $status = 1;
        if (IS_POST && IS_AJAX) {
            $id = I('post.itemID');
            if ($id || $condition && in_array($this->getPk(), array_keys($condition))) {
                $where['id'] = array('IN', $id);
                $where['status'] = array('EQ', 0);
                $where['display'] = array('EQ', 1);
                if (!$statusUp) {
                    $status = 0;
                    $where['status'] = array('EQ', 1);
                }
                $where = array_merge($where, $condition);
                /* 捕获异常 */
                try {
                    $result = $this->where($where)->setField('status', $status);
                } catch (\Exception $e) {
                    $remark = $e->getMessage();
                    /* 记录操作异常日志 */
                    logs_system_error($remark);
                }
            }
        }
        return $result;
    }

    /**
     * 删除 doDelete
     * @param array $condition
     * @param bool $type
     * @return bool|int|mixed
     */
    public function do_delete($condition = array(), $type = false) {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            $id = I('post.itemID');
            if ($id || $condition && in_array($this->getPk(), array_keys($condition))) {
                $where['id'] = array('IN', $id);
                if ($type) {
                    /* 捕获异常 */
                    try {
                        $result = $this->where($where)->delete();
                    } catch (\Exception $e) {
                        $remark = $e->getMessage();
                        /* 记录错误日记 */
                        logs_system_error($remark);
                    }
                } else {
                    $where['display'] = array('EQ', 1);
                    $where = array_merge($where, $condition);
                    /* 捕获异常 */
                    try {
                        $result = $this->where($where)->setField('display', 0);
                    } catch (\Exception $e) {
                        $remark = $e->getMessage();
                        /* 记录操作异常日志 */
                        logs_system_error($remark);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 数据插入成功后操作
     * @param $data
     * @param $options
     */
    protected function _after_insert($data, $options) {
        /* 记录数据操作日志 */
        $remark = '新增数据';
        logs_action_operate($remark, $data, $options);
    }

    protected function _after_update($data, $options) {
        /* 记录数据操作日志 */
        $remark = '编辑数据';
        logs_action_operate($remark, $data, $options);
    }

    protected function _after_delete($data, $options) {
        /* 记录数据操作日志 */
        $remark = '删除数据';
        logs_action_operate($remark, $data, $options);
    }

    /**
     * 自动验证和自动完成函数
     */
    /* 验证标题重复 validate_title_unique */
    protected function validate_title_unique($data) {
        $id = I('post.id');
        $where['id'] = array('NEQ', $id);
        $where['title'] = array('EQ', $data);
        $where['display'] = array('EQ', 1);
        $count = $this->where($where)->count();
        if (empty($count)) {
            return true;
        }
        return false;
    }


	/**
     * 设置置顶 doTop
     * @param array $condition
     * @param string $status
     * @return bool|int
     */
    public function do_top_($condition = array(), $statusUp) {
        $result = 0;
        $top_ = 1;		

        if (IS_POST && IS_AJAX) {
            $id = I('post.itemID');
            if ($id || $condition && in_array($this->getPk(), array_keys($condition))) {
                $where['id'] = array('IN', $id);
                //$where['top_'] = array('EQ', 0);
                $where['display'] = array('EQ', 1);
                if (!$statusUp) {
                    $top_ = 0;
                    //$where['top_'] = array('EQ', 1);
					$where['display'] = array('EQ', 1);
                }
                $where = array_merge($where, $condition);
                /* 捕获异常 */
                try {
                    $result = $this->where($where)->setField('top_', $top_);
					//logs_system_error(json_encode($where).'|'.$top_.'|'.$this->getLastSql());
					//logs_system_error();
                } catch (\Exception $e) {
                    $remark = $e->getMessage();
                    /* 记录操作异常日志 */
                    logs_system_error($remark);
                }
            }
        }
        return $result;
    }


	/**
     * 设置置顶自动下架 do_autodown
     * @param array $condition
     * @param string $status
     * @return bool|int
     */
    public function do_autodown($condition = array(), $statusUp) {
        $result = 0;
        $top_ = 1;		

        if (IS_POST && IS_AJAX) {
            $id = I('post.itemID');
            if ($id || $condition && in_array($this->getPk(), array_keys($condition))) {
                $where['id'] = array('IN', $id);
                //$where['top_'] = array('EQ', 0);
                $where['display'] = array('EQ', 1);
                if (!$statusUp) {
                    $top_ = 0;
                    //$where['top_'] = array('EQ', 1);
					$where['display'] = array('EQ', 1);
                }
                $where = array_merge($where, $condition);
                /* 捕获异常 */
                try {
                    $result = $this->where($where)->setField('autodown', $top_);
					logs_system_error(json_encode($where).'|'.$top_.'|'.$this->getLastSql());
					//logs_system_error();
                } catch (\Exception $e) {
                    $remark = $e->getMessage();
                    /* 记录操作异常日志 */
                    logs_system_error($remark);
                }
            }
        }
        return $result;
    }
}