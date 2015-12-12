<?php
namespace Api\Model;

class TopicReportModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 插入模型数据 操作状态 */

    /* 自动验证 */
    protected $_validate = array(
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('topic_id', 'validate_topicId_check', '{%YZ_topicId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_INSERT),
        array('content', 'require', '{%YZ_content_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
    );
    /* 模型自动完成 */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function')
    );

    /**
     * 举报话题 do_add
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