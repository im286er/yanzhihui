<?php
namespace Manager\Model;
class TopicReportModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array();

    /* 模型自动完成 */
    protected $_auto = array();

    /**
     * 删除 doDelete
     * @param array $condition
     * @param bool $type
     * @return bool|int|mixed
     */
    public function do_delete() {
        $result = 0;
        if (IS_POST && IS_AJAX) {
            $id = I('post.itemID');
            if ($id) {
                $where['id'] = array('IN', $id);
                $where['display'] = array('EQ', 1);
                $topic_id = $this->where($where)->getField('topic_id'); //获取颜图片信息

                $result = $this->where($where)->setField('display', 0);
                /* 删除颜图片 */
                $where_topic['id'] = array('EQ', $topic_id);
                M('Topic')->where($where_topic)->setField('display', 0);
            }
        }
        return $result;
    }
}