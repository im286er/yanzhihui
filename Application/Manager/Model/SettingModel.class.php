<?php
namespace Manager\Model;
class SettingModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
//        array('down_link_ios', 'is_url', '{%YZ_link}', self::VALUE_VALIDATE, 'function'),
//        array('down_link_android', 'is_url', '{%YZ_link}', self::VALUE_VALIDATE, 'function'),
        array('topic_like_count', 'number', '{%YZ_num}', self::VALUE_VALIDATE),
        array('topic_proportion', 'number', '{%YZ_num}', self::VALUE_VALIDATE),
        array('topic_proportion', array(1,2,3,4,5,6,7,8,9), '{%YZ_num}', self::VALUE_VALIDATE, 'in')
    );

    /* 新增和编辑数据的时候允许写入字段 */
    protected $updateFields = 'id,down_link_ios,down_link_android,topic_like_count,topic_proportion';

    /* 数据操作 */
}
