<?php
namespace Home\Model;

use Think\Model;

class TopicModel extends Model {
    /* 字段映射 */
    protected $_map = array();

    /* 自动验证规则 */
    protected $_validate = array();

    /* 模型自动完成 */
    protected $_auto = array();

    /* 新增和编辑数据的时候允许写入字段 */

    /* 数据操作 */
    /**
     * 读取数据 do_data
     */
    public function do_data() {
        $topic_id = I('get.id');
        /* 判断是否存在缓存 */
        $cache = S('HOME_TOPIC_ARTICLE_ID_' . $topic_id);
        if ($cache) {
            $data = $cache;
        } else {
            /* 查询条件 */
            $field = 'topic.id as topic_id,topic.upfile,topic.content,topic.province,topic.city,topic.create_time,
                      user.nick_name,user.sex,user.upfile_head as user_upfile_head';
            $where['topic.id'] = array('EQ', $topic_id);
            $where['topic.status'] = array('EQ', 1);
            $where['topic.display'] = array('EQ', 1);
            $where['user.status'] = array('EQ', 1);
            $where['user.display'] = array('EQ', 1);
            /* 查询数据 */
            $data = $this
                ->alias('topic')
                ->field($field)
                ->where($where)
                ->join('__USER__ user on topic.user_id = user.id')
                ->find();
            /* 读取用户头像 */
            if ($data['user_upfile_head'] && !strstr($data['user_upfile_head'], 'http://')) {
                $data['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $data['user_upfile_head'];
            }
            /* 转换数据 */
            if ($data['content']) {
                $data['content'] = urldecode($data['content']);
            }
            /* 设置缓存 */
            S('HOME_TOPIC_ARTICLE_ID_' . $topic_id, $data, C('CACHE_TIME'));
        }

        /* 下载链接 */
        $data['data_setting'] = S('data_setting');

        return $data;
    }

    /* 自动验证和自动完成函数 */
}