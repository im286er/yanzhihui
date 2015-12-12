<?php
namespace Manager\Model;
class ShopModel extends CommonModel {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array(
        array('shop_account_id', 'require', '{%YZ_enter_shopAccountId}', self::MUST_VALIDATE, 'regex'),
        array('title', 'require', '{%YZ_enter_title}', self::MUST_VALIDATE, 'regex'),
        array('title', 'validate_title_unique', '{%YZ_title_unique_error}', self::MUST_VALIDATE, 'callback'),
        array('address', 'require', '{%YZ_enter_address}', self::MUST_VALIDATE, 'regex'),
        array('longitude', 'require', '{%YZ_enter_maps_address}', self::MUST_VALIDATE, 'regex'),
        array('telephone', 'require', '{%YZ_enter_telephone}', self::MUST_VALIDATE, 'regex'),
        array('telephone', 'number', '{%YZ_enter_telephone}', self::MUST_VALIDATE, 'regex'),
        array('per_capita', 'number', '{%YZ_per_capita}', self::MUST_VALIDATE, 'regex'),
        array('upfile', 'require', '{%YZ_enter_upfile}', self::MUST_VALIDATE, 'regex')
    );

    /* 模型自动完成 */
    protected $_auto = array(
        array('upfile', 'get_upfile', self::MODEL_BOTH, 'function'),
        array('upfile_list', 'get_upfile', self::MODEL_BOTH, 'function'),
        array('status', '0', self::MODEL_BOTH),
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_BOTH, 'function')
    );

    /* 新增和编辑数据的时候允许写入字段 */
    protected $insertFields = 'title,longitude,latitude,province,city,area,address,telephone,per_capita,upfile,upfile_list,shop_account_id';
    protected $updateFields = 'id,title,longitude,latitude,province,city,area,address,telephone,per_capita,upfile,upfile_list,shop_account_id';

    /* 数据操作 */

    /* 自动验证和自动完成函数 */
    /* 验证标题重复 validate_title_unique */
    protected function validate_title_unique($data) {
        $id = I('post.id');
        $where['id'] = array('NEQ', $id);
        $where['shop_account_id'] = array('EQ', UID);
        $where['title'] = array('EQ', $data);
        $where['display'] = array('EQ', 1);
        $count = $this->where($where)->count();
        if (empty($count)) {
            return true;
        }
        return false;
    }
}