<?php
namespace Manager\Model;
class AuthGroupAccessModel extends CommonModel {
    /* 数据操作 */
    /**
     * 修改权限 do_accessUpdate
     */
    public function do_memberUpdate($id = NULL) {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            /* 捕获异常 */
            try {
                /* 定义变量 */
                $itemID = I('post.itemID');
                $dataList = array();

                /* 数据操作 */
                $this->startTrans(); //开启事务
                $where['group_id'] = array('EQ', $id);
                $resultDel = $this->where($where)->delete();

                $itemIDArr = explode(',', $itemID);
                foreach ($itemIDArr as $v) {
                    $dataList[] = array('user_id' => $v, 'group_id' => $id);
                }
                if ($dataList) {
                    $resultAdd = $this->addall($dataList);
                }
                if ($resultDel !== false && $resultAdd) {
                    $this->commit();//提交事务
                    return true;
                } else {
                    $this->rollback(); //事务回滚
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