<?php
namespace Api\Model;

class TopicModel extends CommonModel {
    /* 插入模型数据 操作状态 */
    const MODEL_TOPIC_ADD = 4; //发布话题
    const MODEL_TOPIC_DEL = 5; //删除话题

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array(
        /* 发布话题 */
        array('user_id', 'validate_userId_check', '{%YZ_userId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_TOPIC_ADD),
        array('upfile', 'require', '{%YZ_upfile_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD),
//         array('content', 'require', '{%YZ_content_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD),
//         array('longitude', 'require', '{%YZ_longitude_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD),
//         array('latitude', 'require', '{%YZ_latitude_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD),
//         array('province', 'require', '{%YZ_province_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD),
//         array('city', 'require', '{%YZ_city_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD),
//         array('area', 'require', '{%YZ_area_enter}', self::MUST_VALIDATE, 'regex', self::MODEL_TOPIC_ADD)
        /* 删除话题 */
        array('topic_id', 'validate_topicId_check', '{%YZ_topicId_error}', self::MUST_VALIDATE, 'callback', self::MODEL_TOPIC_DEL),//话题是否存在
        array('topic_id', 'validate_topicId_check_myself', '{%YZ_topicId_myself_error}', self::MUST_VALIDATE, 'callback', self::MODEL_TOPIC_DEL),//验证是否属于自己话题
    );
    /* 模型自动完成 */
    protected $_auto = array(
        /* 发布话题 */
        array('upfile', 'get_upfile', self::MODEL_TOPIC_ADD, 'function'),
        array('create_time', 'time', self::MODEL_TOPIC_ADD, 'function')
    );

    /**
     * 话题列表 do_index
     */
    public function do_index() {
        /* 初始化变量 */
        $sex = I('get.sex');
        $city = I('get.city');
        $get_user_id = I('get.get_user_id'); //查看某 user_id 的颜图片
        $user_id = I('get.user_id');
        $user_id = empty($user_id) ? 0 : $user_id;
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;

        /* 过滤屏蔽用户 */
        if ($user_id && empty($get_user_id)) {
            $where_user_blocked['user_id'] = array('EQ', $user_id);
            $list_user_blocked = M('UserBlocked')->where($where_user_blocked)->getField('to_user_id', true);
        }

        $cache = S('TOPIC_INDEX_USER_ID_' . $user_id . '_GET_USER_ID_' . $get_user_id . '_SEX_' . $sex . '_CITY_' . $city);
        /* 判断是否存在缓存 */
        if ($cache) {
            $list = $cache;
        } else {
            /* 查询条件 */
            $field = 'topic.id,topic.upfile,topic.content,topic.province,topic.city,topic.longitude,topic.latitude,topic.create_time,
                      user.id as user_id,user.nick_name,user.sex,user.upfile_head as user_upfile_head,topic.like_count,
                      0 as comment_count,0 as is_like,0 as attention_relation';
            $where['topic.status'] = array('EQ', 1);
            $where['topic.display'] = array('EQ', 1);
            $where['user.status'] = array('EQ', 1);
            $where['user.display'] = array('EQ', 1);
            if ($sex && in_array($sex, array(1, 2))) {
                $where['user.sex'] = array('EQ', $sex);
            }
            if ($city) {
                $where['topic.city'] = array('EQ', $city);
            }
            if ($get_user_id) {
                $where['topic.user_id'] = array('EQ', $get_user_id);
            }
            $order = 'topic.id desc';
            /* 查询数据 */
            $list = $this
                ->alias('topic')
                ->field($field)
                ->where($where)
                ->join('__USER__ user on topic.user_id = user.id')
                ->order($order)
                ->limit(C('PAGE_NUM_LIST') * C('PAGE_NUM_MAX'))
                ->select();

            /* 设置缓存 */
            S('TOPIC_INDEX_USER_ID_' . $user_id . '_GET_USER_ID_' . $get_user_id . '_SEX_' . $sex . '_CITY_' . $city, $list, C('CACHE_TIME'));
        }

        /* 随机插入热门信息 */
        if (empty($get_user_id)) {
            $list = $this->list_hot($list);
        }

        /* 过滤屏蔽用户 */
        foreach ($list as $k => $v) {
            if (in_array($v['user_id'], $list_user_blocked)) {
                unset($list[$k]);
            }
        }

        /* 读取分页数据 */
        $list = empty($list) ? array() : $list;
        $list_result_page = array_slice($list, ($page_num - 1) * C('PAGE_NUM_LIST'), C('PAGE_NUM_LIST'));

        if ($list_result_page) {
            /* 读取列表 ID */
            $list_result_topic_id = array();
            $list_result_user_id = array();

            foreach ($list_result_page as $k => $v) {
                $list_result_topic_id[] = $v['id'];
                $list_result_user_id[] = $v['user_id'];

                /* 读取颜图片 */
                if ($v['upfile']) {
                    $list_result_page[$k]['upfile'] = C('APP_URL') . '/Uploads/Images/Topic/' . $v['upfile'];
                }
                /* 读取用户头像 */
                if ($v['user_upfile_head'] && !strstr($v['user_upfile_head'], 'http://')) {
                    $list_result_page[$k]['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['user_upfile_head'];
                }
                /* 默认值 */
                $list_result_page[$k]['list_like'] = array();
            }

            if ($user_id) {
                /* 查询是否点过赞 */
                $whereUserLike['user_id'] = array('EQ', $user_id);
                $whereUserLike['topic_id'] = array('IN', $list_result_topic_id);
                $listUserLikeTopicId = M('TopicLike')->where($whereUserLike)->getField('topic_id', true);

                /* 查询关注状态 */
                $whereAttentionRelation['user_id'] = array('EQ', $user_id);
                $listAttentionRelation = M('UserAttention')->field('to_user_id,relation')->where($whereAttentionRelation)->select();

                /* 遍历合并数组 */
                foreach ($list_result_page as $k => $v) {
                    if (in_array($v['id'], $listUserLikeTopicId)) {
                        $list_result_page[$k]['is_like'] = strval(1);
                    }

                    foreach ($listAttentionRelation as $key => $value) {
                        if ($v['user_id'] == $value['to_user_id']) {
                            $list_result_page[$k]['attention_relation'] = $value['relation'];
                        }
                    }
                }
            }

            /* 查询相应点赞 */
            $list_topic_conversion = array();
            $field_user_list = 'topic_like.topic_id,topic_like.user_id,user.upfile_head';
            $where_user_list['topic_like.topic_id'] = array('IN', implode(',', $list_result_topic_id));
            $where_user_list['user.status'] = array('EQ', 1);
            $where_user_list['user.display'] = array('EQ', 1);
            $order_user_list = 'topic_like.create_time desc';
            $list_user_like = M('TopicLike')
                ->alias('topic_like')
                ->field($field_user_list)
                ->where($where_user_list)
                ->join('__USER__ user on topic_like.user_id = user.id')
                ->order($order_user_list)
                ->select();

            foreach ($list_user_like as $k => $v) {
                /* 读取头像 */
                if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                    $v['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
                }
                $list_topic_conversion[$v['topic_id']][] = $v;
            }

            foreach ($list_topic_conversion as $k => $v) {
                $list_topic_conversion[$k] = array_slice($v, 0, 9);
            }

            /* 查询点赞总数和评论总数 */
            $where_like_count['id'] = array('IN', implode(',', $list_result_topic_id));
            $data_topic_result = $this->where($where_like_count)->getField('id,like_count,comment_count');

            /* 遍历数组 */
            foreach ($list_result_page as $k => $v) {
                if (!empty($list_topic_conversion[$v['id']])) {
                    $list_result_page[$k]['list_like'] = $list_topic_conversion[$v['id']];
                }
                if ($data_topic_result[$v['id']]['like_count']) {
                    $list_result_page[$k]['like_count'] = $data_topic_result[$v['id']]['like_count'];
                }
                if ($data_topic_result[$v['id']]['comment_count']) {
                    $list_result_page[$k]['comment_count'] = $data_topic_result[$v['id']]['comment_count'];
                }
            }
        }

        /* 读取json */
        $jsonInfo['list'] = arr_content_replace($list_result_page);
        return $jsonInfo;
    }

    /**
     * 热门排序
     */
    protected function list_hot($source_array = array()) {
        $data_setting = S('data_setting');
        $co = $data_setting['topic_proportion']; //系数
        $like_count = $data_setting['topic_like_count']; //基数

        if ($like_count && $co) {
            $h = array(); //大于的基数
            $n = array(); //小于的基数
            for ($i = 0; $i < count($source_array); $i++) {
                $target = $source_array[$i];
                $target['like_count'] > $like_count ? array_push($h, $target) : array_push($n, $target);
            }
            $cnt = intval(count($n) / 10);
            shuffle($h); //随机大于的基数

            for ($i = 0; $i < $cnt; $i++) {
                for ($j = 0; $j < $co; $j++) {
                    $r = rand(10 * $i, (10 * $i + 10));
                    $output = array_slice($h, 0, 1);
                    $h = array_slice($h, 1);
                    array_splice($n, $r, 0, $output);
                }
            }
            $result_source_array = array_filter(array_merge($n, $h));
            return $result_source_array;
        }
        return $source_array;
    }

    /**
     * 关注话题列表 do_index_attention
     */
    public function do_index_attention() {
        /* 初始化变量 */
        $sex = I('get.sex');
        $user_id = I('get.user_id');
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;
        $page_num = $page_num > C('PAGE_NUM_MAX') ? C('PAGE_NUM_MAX') : $page_num;

        /* 过滤屏蔽用户 */
        if ($user_id) {
            $where_user_blocked['user_id'] = array('EQ', $user_id);
            $list_user_blocked = M('UserBlocked')->where($where_user_blocked)->getField('to_user_id', true);
        }

        /* 判断是否存在缓存 */
        $cache = S('TOPIC_INDEX_ATTENTION_USER_ID_' . $user_id . '_SEX_' . $sex);
        if ($cache) {
            $list = $cache;
        } else {
            /* 查询条件 */
            $field = 'topic.id,topic.upfile,topic.content,topic.province,topic.city,topic.longitude,topic.latitude,topic.create_time,
                  user.id as user_id,user.nick_name,user.sex,user.upfile_head as user_upfile_head,topic.like_count,
                  0 as comment_count,0 as is_like,0 as attention_relation';
            $where['user_attention.user_id'] = array('EQ', $user_id);
            $where['topic.status'] = array('EQ', 1);
            $where['topic.display'] = array('EQ', 1);
            $where['user.status'] = array('EQ', 1);
            $where['user.display'] = array('EQ', 1);
            if ($sex && in_array($sex, array(1, 2))) {
                $where['user.sex'] = array('EQ', $sex);
            }
            $order = 'topic.id desc';
            /* 查询数据 */
            $list = $this
                ->alias('topic')
                ->field($field)
                ->where($where)
                ->join('__USER__ user on topic.user_id = user.id')
                ->join('__USER_ATTENTION__ user_attention on user_attention.to_user_id = topic.user_id')
                ->order($order)
                ->group('topic.id')
                ->limit(C('PAGE_NUM_LIST') * C('PAGE_NUM_MAX'))
                ->select();

            /* 设置缓存 */
            S('TOPIC_INDEX_ATTENTION_USER_ID_' . $user_id . '_SEX_' . $sex, $list, C('CACHE_TIME'));
        }

        /* 过滤屏蔽用户 */
        foreach ($list as $k => $v) {
            if (in_array($v['user_id'], $list_user_blocked)) {
                unset($list[$k]);
            }
        }

        /* 读取分页数据 */
        $list = empty($list) ? array() : $list;
        $list_result_page = array_slice($list, ($page_num - 1) * C('PAGE_NUM_LIST'), C('PAGE_NUM_LIST'));

        if ($list_result_page) {
            /* 遍历列表数据 */
            $list_result_topic_id = array();
            $list_user_id = array();

            foreach ($list_result_page as $k => $v) {
                $list_result_topic_id[] = $v['id'];
                $list_user_id[] = $v['user_id'];

                /* 读取颜图片 */
                if ($v['upfile']) {
                    $list_result_page[$k]['upfile'] = C('APP_URL') . '/Uploads/Images/Topic/' . $v['upfile'];
                }
                /* 读取用户头像 */
                if ($v['user_upfile_head'] && !strstr($v['user_upfile_head'], 'http://')) {
                    $list_result_page[$k]['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['user_upfile_head'];
                }

                /* 默认值 */
                $list_result_page[$k]['list_like'] = array();
            }

            /* 查询是否点过赞 */
            $whereUserLike['user_id'] = array('EQ', $user_id);
            $whereUserLike['topic_id'] = array('IN', $list_result_topic_id);
            $listUserLikeTopicId = M('TopicLike')->where($whereUserLike)->getField('topic_id', true);

            /* 查询关注状态 */
            $whereAttentionRelation['user_id'] = array('EQ', $user_id);
            $listAttentionRelation = M('UserAttention')->field('to_user_id,relation')->where($whereAttentionRelation)->select();

            /* 遍历合并数组 */
            foreach ($list_result_page as $k => $v) {
                if (in_array($v['id'], $listUserLikeTopicId)) {
                    $list_result_page[$k]['is_like'] = '1';
                }
                foreach ($listAttentionRelation as $key => $value) {
                    if ($v['user_id'] == $value['to_user_id']) {
                        $list_result_page[$k]['attention_relation'] = $value['relation'];
                    }
                }
            }

            /* 查询相应点赞 */
            $list_topic_conversion = array();
            $field_user_list = 'topic_like.topic_id,topic_like.user_id,user.upfile_head';
            $where_user_list['topic_like.topic_id'] = array('IN', implode(',', $list_result_topic_id));
            $where_user_list['user.status'] = array('EQ', 1);
            $where_user_list['user.display'] = array('EQ', 1);
            $order_user_list = 'topic_like.create_time desc';
            $list_user_like = M('TopicLike')
                ->alias('topic_like')
                ->field($field_user_list)
                ->where($where_user_list)
                ->join('__USER__ user on topic_like.user_id = user.id')
                ->order($order_user_list)
                ->select();

            foreach ($list_user_like as $k => $v) {
                /* 读取头像 */
                if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                    $v['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
                }
                $list_topic_conversion[$v['topic_id']][] = $v;
            }
            foreach ($list_topic_conversion as $k => $v) {
                $list_topic_conversion[$k] = array_slice($v, 0, 9);
            }

            /* 查询点赞总数和评论总数 */
            $where_like_count['id'] = array('IN', implode(',', $list_result_topic_id));
            $data_topic_result = $this->where($where_like_count)->getField('id,like_count,comment_count');

            /* 遍历数组 */
            foreach ($list_result_page as $k => $v) {
                if (!empty($list_topic_conversion[$v['id']])) {
                    $list_result_page[$k]['list_like'] = $list_topic_conversion[$v['id']];
                }
                if ($data_topic_result[$v['id']]['like_count']) {
                    $list_result_page[$k]['like_count'] = $data_topic_result[$v['id']]['like_count'];
                }
                if ($data_topic_result[$v['id']]['comment_count']) {
                    $list_result_page[$k]['comment_count'] = $data_topic_result[$v['id']]['comment_count'];
                }
            }
        }

        /* 读取json */
        $jsonInfo['list'] = arr_content_replace($list_result_page);
        return $jsonInfo;
    }

    /**
     * 排行话题列表 do_index_rank
     */
    public function do_index_rank() {
        /* 初始化变量 */
        $sex = I('get.sex');
        $city = I('get.city');
        $order_type = I('get.order_type');
        $user_id = I('get.user_id');
        $user_id = empty($user_id) ? 0 : $user_id;

        /* 判断是否存在缓存 */
        $cache = S('TOPIC_INDEX_RANK_ORDER_TYPE_' . $order_type . '_SEX_' . $sex);
        if ($cache) {
            $list = $cache;
        } else {
            /* 读取点赞并排序用户*/
            $now_date = strtotime(date('Y-m-d')); //今天时间戳
            $fieldTopicLike_order = 'topic.user_id,count(*) as like_count,';
            $orderTopicLike = 'like_count desc';
            $groupTopicLike = 'user_id';

            switch ($order_type) {
                case 1: //全国今日热榜（当日0点开始到现在）
                    $whereTopicLike['topic_like.create_time'] = array('GT', $now_date);
                    break;
                case 2: //全国本周热榜（当周周一0点开始到现在）
                    $now_data_time = strtotime('last monday');
                    if (date('w') == 1) { //今天星期一
                        $now_data_time = strtotime('monday');
                    }
                    $whereTopicLike['topic_like.create_time'] = array('GT', $now_data_time);
                    break;
                case 3: // 全国总榜
                    $fieldTopicLike_order = 'user.id as user_id,user.like_count,';
                    break;
                case 4: // 同城今日热榜（如果没有获取到用户地理位置，那么默认认为他在广州）
                    $whereTopicLike['user.city'] = array('EQ', $city);
                    $whereTopicLike['topic_like.create_time'] = array('GT', $now_date);
                    break;
                case 5: // 同城本周热榜（如果没有获取到用户地理位置，那么默认认为他在广州）
                    $now_data_time = strtotime('last monday');
                    if (date('w') == 1) { //今天星期一
                        $now_data_time = strtotime('monday');
                    }
                    $whereTopicLike['user.city'] = array('EQ', $city);
                    $whereTopicLike['topic_like.create_time'] = array('GT', $now_data_time);
                    break;
                case 6: // 同城总榜
                    $whereTopicLike['user.city'] = array('EQ', $city);
                    break;
            }

            $whereTopicLike['topic.status'] = array('EQ', 1);
            $whereTopicLike['topic.display'] = array('EQ', 1);
            $whereTopicLike['user.status'] = array('EQ', 1);
            $whereTopicLike['user.display'] = array('EQ', 1);
            if ($sex && in_array($sex, array(1, 2))) {
                $whereTopicLike['user.sex'] = array('EQ', $sex);
            }
            $fieldTopicLike = $fieldTopicLike_order . 'user.nick_name,user.sex,user.upfile_head as user_upfile_head,user.province,user.city,0 as attention_relation';

            $list = M('TopicLike')
                ->alias('topic_like')
                ->field($fieldTopicLike)
                ->where($whereTopicLike)
                ->join('__TOPIC__ topic on topic_like.topic_id = topic.id')
                ->join('__USER__ user on topic.user_id = user.id')
                ->order($orderTopicLike)
                ->group($groupTopicLike)
                ->limit(20)
                ->select();

            /* 设置缓存 */
            S('TOPIC_INDEX_RANK_ORDER_TYPE_' . $order_type . '_SEX_' . $sex, $list, C('CACHE_TIME'));
        }

        if ($list) {
            $list_user_id = array();
            foreach ($list as $k => $v) {
                /* 默认相关图集 */
                $list[$k]['list_user_topic'] = array();
                /* 读取用户头像 */
                if ($v['user_upfile_head'] && !strstr($v['user_upfile_head'], 'http://')) {
                    $list[$k]['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['user_upfile_head'];
                }

                $list_user_id[] = $v['user_id'];
            }

            /* 查询关注状态 */
            if ($list_user_id && $user_id) {
                $whereAttentionRelation['user_id'] = array('EQ', $user_id);
                $listAttentionRelation = M('UserAttention')->field('to_user_id,relation')->where($whereAttentionRelation)->select();
                /* 遍历合并数组 */
                foreach ($list as $k => $v) {
                    foreach ($listAttentionRelation as $key => $value) {
                        if ($v['user_id'] == $value['to_user_id']) {
                            $list[$k]['attention_relation'] = $value['relation'];
                        }
                    }
                }
            }

            /* 查询相应话题 */
            $list_topic_conversion = array();
            $field_list_topic = 'id as topic_id,upfile as upfile_topic,user_id';
            $where_list_topic['user_id'] = array('IN', implode(',', $list_user_id));
            $where_list_topic['status'] = array('EQ', 1);
            $where_list_topic['display'] = array('EQ', 1);
            $order_list_topic = 'topic_id desc';
            $list_topic = $this->field($field_list_topic)->where($where_list_topic)->order($order_list_topic)->select();
            foreach ($list_topic as $k => $v) {
                /* 读取颜图片 */
                if ($v['upfile_topic']) {
                    $v['upfile_topic'] = C('APP_URL') . '/Uploads/Images/Topic/' . $v['upfile_topic'];
                }

                $list_topic_conversion[$v['user_id']][] = $v;
            }
            foreach ($list_topic_conversion as $k => $v) {
                $list_topic_conversion[$k] = array_slice($v, 0, 3);
            }

            /* 遍历数组 */
            foreach ($list as $k => $v) {
                if ($list_topic_conversion[$v['user_id']]) {
                    $list[$k]['list_user_topic'] = $list_topic_conversion[$v['user_id']];
                }
            }
        }

        /* 读取json */
        $jsonInfo['list'] = arr_content_replace($list);
        return $jsonInfo;
    }

    /**
     * 话题详情 do_article
     */
    public function do_article() {
        /* 初始化变量 */
        $topic_id = I('get.topic_id');
        $user_id = I('get.user_id');
        $user_id = empty($user_id) ? 0 : $user_id;

        /* 判断是否存在缓存 */
        $cache = S('TOPIC_ARTICLE_ID_' . $topic_id);
        if ($cache) {
            $data = $cache;
        } else {
            /* 查询条件 */
            $field = 'topic.upfile,topic.content,topic.province,topic.city,topic.longitude,topic.latitude,topic.comment_count,topic.create_time,
                      user.id as user_id,user.nick_name,user.sex,user.upfile_head as user_upfile_head,0 as is_be_shielded';
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
            if ($data) {
                /* 读取用户头像 */
                if ($data['user_upfile_head'] && !strstr($data['user_upfile_head'], 'http://')) {
                    $data['user_upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $data['user_upfile_head'];
                }
                /* 读取图片 */
                if ($data['upfile']) {
                    $data['upfile'] = C('APP_URL') . '/Uploads/Images/Topic/' . $data['upfile'];
                }
            }
            /* 设置缓存 */
            S('TOPIC_ARTICLE_ID_' . $topic_id, $data);
        }

        if ($data) {
            /* 查询评论数 */
            $where_comment['id'] = array('EQ', $topic_id);
            $data['comment_count'] = $this->where($where_comment)->getField('comment_count');

            /* 判断用户是否点过赞 */
            $userLikeCount = strval(0);
            if ($user_id) {
                $whereUserLike['user_id'] = array('EQ', $user_id);
                $whereUserLike['topic_id'] = array('EQ', $topic_id);
                $userLikeCount = M('TopicLike')->where($whereUserLike)->count();
            }
            $data['is_like'] = $userLikeCount;

            /* 查询点赞总数 */
            $where_like_count['id'] = array('EQ', $topic_id);
            $like_count = $this->where($where_like_count)->getField('like_count');
            $data['like_count'] = $like_count;

            /* 查询点赞 */
            $fieldLike = 'topic_like.user_id,user.upfile_head';
            $whereLike['topic_like.topic_id'] = array('EQ', $topic_id);
            $whereLike['user.status'] = array('EQ', 1);
            $whereLike['user.display'] = array('EQ', 1);
            $orderLike = 'topic_like.create_time desc';
            $listLike = M('TopicLike')
                ->alias('topic_like')
                ->field($fieldLike)
                ->where($whereLike)
                ->join('__USER__ user on topic_like.user_id = user.id')
                ->order($orderLike)
                ->limit(30)
                ->select();
            foreach ($listLike as $k => $v) {
                if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                    $listLike[$k]['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
                }
            }
            $data['list_like'] = $listLike;

            /* 查询关注状态 */
            if ($user_id) {
                $whereAttentionRelation['user_id'] = array('EQ', $user_id);
                $whereAttentionRelation['to_user_id'] = array('EQ', $data['user_id']);
                $userAttentionRelation = M('UserAttention')->where($whereAttentionRelation)->getField('relation');
            }
            $userAttentionRelation = empty($userAttentionRelation) ? strval(0) : $userAttentionRelation;
            $data['attention_relation'] = $userAttentionRelation;

            /* 查询是否被屏蔽 */
            if ($user_id) {
                $where_is_be_shielded['user_id'] = array('EQ', $data['user_id']);
                $where_is_be_shielded['to_user_id'] = array('EQ', $user_id);
                $count_is_be_shielded = M('UserBlocked')->where($where_is_be_shielded)->count();
                $data['is_be_shielded'] = $count_is_be_shielded;
            }
        }
        $return_data['data'] = arr_content_replace($data);
        /* 读取json */
        return $return_data;
    }

    /**
     * 发布话题 do_add
     */
    public function do_add() {
        if ($this->create('', self::MODEL_TOPIC_ADD)) {
            $field = 'user_id,upfile,content,longitude,latitude,province,city,area,create_time';
            $result = $this->field($field)->add();
            /* 发布成功 */
            if ($result) {
                /* 用户表累加发布数 */
                $where['id'] = array('EQ', I('post.user_id'));
                M('User')->where($where)->setInc('topic_count');
            }
            return $result;
        }
        return false;
    }

    /**
     * 用户删除自己的话题
     */
    public function do_delete() {
        if ($this->create('', self::MODEL_TOPIC_DEL)) {
            $user_id = I('get.user_id');
            $sex = I('post.sex');
            $city = I('post.city');
            $get_user_id = I('post.get_user_id'); //查看某 user_id 的颜图片
            //验证本话题已获得多少颜值
            $topicid = I('post.topic_id');
            $where['id'] = $topicid;
            $topicinfo = $this->field('user_id,like_count')->where($where)->find();
            $userInfo = M('User')->field('like_count,like_now_count,topic_count,topic_like_count,topic_comment_count')->where(array('id' => $topicinfo['user_id']))->find();
            if ($userInfo['like_count'] >= $topicinfo['like_count'] && $userInfo['like_now_count'] >= $topicinfo['like_count']) {
                $result = $this->where($where)->setField('display', 0); //删除话题
                M('Topic_comment')->where(array('topic_id' => $topicid))->setField('display', 0); //删除当前话题的评论
                if ($result) {
                    //清空当前用户缓存
                    $this->deleteCache($user_id, $get_user_id, $topicid, $sex);
                    /* User表减去点数*/
                    $user_where['like_count'] = $userInfo['like_count'] - $topicinfo['like_count']; //用户总颜值
                    $user_where['like_now_count'] = $userInfo['like_now_count'] - $topicinfo['like_count'];   //当前用户颜值
                    $user_where['topic_count'] = $userInfo['topic_count'] - 1; //用户话题总数-1
                    $topiclike_count = M('Topic_like')->where(array('topic_id' => $topicid))->count();
                    $topic_comment_count = M('Topic_comment')->where(array('topic_id' => $topicid))->count();
                    $user_where['topic_like_count'] = $userInfo['topic_like_count'] - $topiclike_count;//话题总赞数
                    $user_where['topic_comment_count'] = $userInfo['topic_comment_count'] - $topic_comment_count;      //总评论数
                    $bool = M('User')->where(array('id' => $topicinfo['user_id']))->save($user_where);
                    return $bool;
                }
            } else { //话题颜值大于用户当前颜值数，退出，不能删除
                $this->error = L('YZ_topic_countlike_error');
                return false;
            }
        }
        return false;
    }

    /* 自动验证和自动完成函数 */

    /* 判断是否为自己的话题 */

    protected function deleteCache($user_id, $get_user_id, $topicid, $sex) {
        S('TOPIC_INDEX_USER_ID_' . $user_id . '_GET_USER_ID_' . $get_user_id . '_SEX_' . $sex . '_CITY_', NULL);
        S('TOPIC_INDEX_USER_ID_' . $user_id . '_GET_USER_ID_' . $get_user_id . '_SEX_' . 0 . '_CITY_', NULL);
        S('TOPIC_INDEX_USER_ID_' . $user_id . '_GET_USER_ID_' . $get_user_id . '_SEX_' . 1 . '_CITY_', NULL);
        S('TOPIC_INDEX_USER_ID_' . $user_id . '_GET_USER_ID_' . $get_user_id . '_SEX_' . 2 . '_CITY_', NULL);
        S('TOPIC_ARTICLE_ID_' . $topicid, NULL);//清空当前话题详情缓存
        //S('TOPIC_INDEX_RANK_ORDER_TYPE_' . $order_type . '_SEX_' . $sex, null); //排行缓存
    }

    /* 删除清空缓存 */

    public function validate_topicId_check_myself($data) {
        if ($data) {
            $user_id = I('get.user_id'); //登陆id
            $topic_id = I('post.topic_id');
            /* 查询数据 */
            $where['id'] = array('EQ', $data);
            $where['user_id'] = array('EQ', $user_id);
            $where['topic_id'] = array('EQ', $topic_id);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            $count = M('Topic')->where($where)->count();
            if ($count == 1)
                return true;
        }
        return false;
    }
}