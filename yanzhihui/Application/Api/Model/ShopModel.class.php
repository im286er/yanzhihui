<?php
namespace Api\Model;

class ShopModel extends CommonModel {
    /* 插入模型数据 操作状态 */

    /* 字段映射 */
    protected $_map = array();

    /* 自动验证 */
    protected $_validate = array();
    /* 模型自动完成 */
    protected $_auto = array();

    /**
     * 商家列表 do_index
     */
    public function do_index() {
        /* 初始化变量 */
        $user_id = I('get.user_id');
        $city = I('get.city');
        $current_latitude = I('get.current_latitude');
        $current_latitude = empty($current_latitude) ? 1 : $current_latitude;
        $current_longitude = I('get.current_longitude');
        $current_longitude = empty($current_longitude) ? 1 : $current_longitude;
        $page_num = I('get.page_num');
        $page_num = empty($page_num) || $page_num < 0 ? 1 : $page_num;
        $list_result_page2 = array();

        /* 判断是否存在缓存 */
        $cache = S('SHOP_INDEX_USER_ID_' . $user_id . '_CITY_' . $city);
        if ($cache) {
            $list = $cache;
        } else {
            /* 查询条件 */
            $field = 'id,title,address,upfile,fun_distance(' . $current_latitude . ',' . $current_longitude . ',latitude,longitude) as distance_for_me,0 as want_count,0 as is_want';
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            if ($city) {
                $where['topic.city'] = array('EQ', $city);
            }
            $order = 'distance_for_me asc,id desc';
            /* 查询数据 */
            $list = $this->field($field)->where($where)->order($order)->limit(C('PAGE_NUM_LIST') * C('PAGE_NUM_MAX'))->select();

            /* 设置缓存 */
            S('SHOP_INDEX_USER_ID_' . $user_id . '_CITY_' . $city, $list, C('CACHE_TIME'));
        }

        /* 读取分页数据 */
        $list = empty($list) ? array() : $list;
        $list_result_page = array_slice($list, ($page_num - 1) * C('PAGE_NUM_LIST'), C('PAGE_NUM_LIST'));

        if ($list_result_page) {
            $list_result_shop_id = array();
            foreach ($list_result_page as $k => $v) {
                /* 读取商家图片 */
                if ($v['upfile']) {
                    $list_result_page[$k]['upfile'] = C('APP_URL') . '/Uploads/Images/Shop/' . $v['upfile'];
                }
                /* 转换数据 */
                $list_result_page[$k]['title'] = htmlspecialchars_decode($v['title']);

                /* 默认值 */
                $list_result_page[$k]['list_coupon'] = array();
                $list_result_page[$k]['list_want'] = array();

                $list_result_shop_id[] = $v['id'];
            }

            /* 查询是否点过我想去 */
            if ($list_result_shop_id && $user_id) {
                $whereUserLike['user_id'] = array('EQ', $user_id);
                $whereUserLike['shop_id'] = array('IN', implode(',', $list_result_shop_id));
                $whereUserLike['content'] = array('EQ', '想去');
                $listUserLikeShopId = M('ShopUserWent')->where($whereUserLike)->getField('shop_id', true);

                /* 遍历合并数组 */
                foreach ($list_result_page as $k => $v) {
                    $list_result_page[$k]['is_want'] = in_array($v['id'], $listUserLikeShopId) ? strval(1) : strval(0);
                }
            }

            /* 查询优惠劵 */
            $list_coupon_conversion = array();
            $field_coupon = 'shop_id,id as coupon_id,title as coupon_title,coupon_type,coupon_worth';
            $where_coupon['shop_id'] = array('IN', implode(',', $list_result_shop_id));
            $where_coupon['status'] = array('EQ', 1);
            $where_coupon['display'] = array('EQ', 1);
            $order_coupon = 'coupon_type asc,coupon_worth asc,id desc';
            $list_coupon = M('ShopCoupon')
                ->field($field_coupon)
                ->where($where_coupon)
                ->order($order_coupon)
                ->select();
            foreach ($list_coupon as $k => $v) {
                $v['coupon_title'] = htmlspecialchars_decode($v['coupon_title']);

                if ($v['coupon_type'] == 1) {
                    $v['coupon_tag'] = strval(0);
                } else {
                    $v['coupon_tag'] = $v['coupon_worth'];
                }
                unset($v['coupon_type']);
                unset($v['coupon_worth']);

                $list_coupon_conversion[$v['shop_id']][] = $v;
            }

            foreach ($list_coupon_conversion as $k => $v) {
                $list_coupon_conversion[$k] = array_slice($v, 0, 4);
            }

            /* 查询去向列表 */
            $list_user_went_conversion = array();
            $field_user_went = 'shop_user_went.shop_id,shop_user_went.user_id,user.upfile_head';
            $where_user_went['shop_user_went.shop_id'] = array('IN', implode(',', $list_result_shop_id));
            $where_user_went['user.status'] = array('EQ', 1);
            $where_user_went['user.display'] = array('EQ', 1);
            $order_user_went = 'shop_user_went.create_time desc';
            $list_user_went = M('ShopUserWent')
                ->alias('shop_user_went')
                ->field($field_user_went)
                ->where($where_user_went)
                ->join('__USER__ user on shop_user_went.user_id = user.id')
                ->order($order_user_went)
                ->group('shop_user_went.shop_id,shop_user_went.user_id')
                ->select();

            foreach ($list_user_went as $k => $v) {
                if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                    $v['upfile_head'] = C('APP_URL') . '/Uploads/Images/User/' . $v['upfile_head'];
                }

                $list_user_went_conversion[$v['shop_id']][] = $v;
            }
            foreach ($list_user_went_conversion as $k => $v) {
                $list_user_went_conversion[$k] = array_slice($v, 0, 9);
            }

            /* 查询去向总数 */
            $count_user_went_conversion = array();
            $count_user_went = M('ShopUserWent')
                ->alias('shop_user_went')
                ->field('count(1) as count,shop_user_went.shop_id')
                ->where($where_user_went)
                ->join('__USER__ user on shop_user_went.user_id = user.id')
                ->group('shop_user_went.shop_id')
                ->select();

            foreach ($count_user_went as $k => $v) {
                $count_user_went_conversion[$v['shop_id']] = $v['count'];
            }

            /* 遍历数组 */
            foreach ($list_result_page as $k => $v) {
                if (!empty($list_coupon_conversion[$v['id']])) {
                    $list_result_page[$k]['list_coupon'] = $list_coupon_conversion[$v['id']];
                }
                if (!empty($list_user_went_conversion[$v['id']])) {
                    $list_result_page[$k]['list_want'] = $list_user_went_conversion[$v['id']];
                }
                if (!empty($count_user_went_conversion[$v['id']])) {
                    $list_result_page[$k]['want_count'] = $count_user_went_conversion[$v['id']];
                }
            }

            /* 遍历删除不存在优惠劵的商家 */
            foreach ($list_result_page as $k => $v) {
                if (count($v['list_coupon'])) {
                    $list_result_page2[] = $v;
                }
            }
        }

        /* 读取json */
        $jsonInfo['list'] = arr_content_replace($list_result_page2);
        return $jsonInfo;
    }

    /**
     * 商家详情 do_article
     */
    public function do_article() {
        /* 初始化变量 */
        $shop_id = I('get.shop_id');
        $user_id = I('get.user_id');
        $user_id = empty($user_id) ? 0 : $user_id;

        /* 判断是否存在缓存 */
        $cache = S('SHOP_ARTICLE_ID_' . $shop_id);
        if ($cache) {
            $data = $cache;
        } else {
            /* 查询条件 */
            $field = 'title,address,longitude,latitude,telephone,per_capita,upfile,upfile_list';
            $where['id'] = array('EQ', $shop_id);
            $where['status'] = array('EQ', 1);
            $where['display'] = array('EQ', 1);
            /* 查询数据 */
            $data = $this
                ->alias('topic')
                ->field($field)
                ->where($where)
                ->find();
            if ($data) {
                /* 过滤数据 */
                $data['title'] = htmlspecialchars_decode($data['title']);

                /* 读取图片 */
                if ($data['upfile']) {
                    $data['upfile'] = C('APP_URL') . '/Uploads/Images/Shop/' . $data['upfile'];
                }
                $data['upfile_album'] = array();
                if ($data['upfile_list']) {
                    foreach (explode(',', $data['upfile_list']) as $v) {
                        $data['upfile_album'][] = C('APP_URL') . '/Uploads/Images/Shop/' . $v;
                    }
                }
                unset($data['upfile_list']);
            }

            /* 设置缓存 */
            S('SHOP_ARTICLE_ID_' . $shop_id, $data, C('CACHE_TIME'));
        }

        if ($data) {
            /* 查询优惠劵 */
            $fieldCoupon = 'id as coupon_id,title as coupon_title,coupon_type,coupon_worth,content as coupon_content,like_consume_count';
            $whereCoupon['shop_id'] = array('EQ', $shop_id);
            $whereCoupon['status'] = array('EQ', 1);
            $whereCoupon['display'] = array('EQ', 1);
            $orderCoupon = 'coupon_type asc,coupon_worth asc,id desc';
            $listCoupon = M('ShopCoupon')->field($fieldCoupon)->where($whereCoupon)->order($orderCoupon)->limit(8)->select();

            foreach ($listCoupon as $k => $v) {
                /* 过滤数据 */
                $listCoupon[$k]['coupon_title'] = htmlspecialchars_decode($v['coupon_title']);
                $listCoupon[$k]['coupon_content'] = htmlspecialchars_decode($v['coupon_content']);

                if ($v['coupon_type'] == 1) {
                    $listCoupon[$k]['coupon_tag'] = strval(0);
                } else {
                    $listCoupon[$k]['coupon_tag'] = $v['coupon_worth'];
                }
                unset($listCoupon[$k]['coupon_type']);
                unset($listCoupon[$k]['coupon_worth']);
            }
            $data['list_coupon'] = $listCoupon;

            /* 判断用户是否点过我想去和我去过 */
            if ($user_id) {
                $whereUserLike['shop_id'] = array('EQ', $shop_id);
                $whereUserLike['user_id'] = array('EQ', $user_id);
                $listUserLike = M('ShopUserWent')->where($whereUserLike)->getField('content', true);
            }

            $data['is_want'] = in_array('想去', $listUserLike) ? strval(1) : strval(0); //我想去
            $data['is_been'] = in_array('去过', $listUserLike) ? strval(1) : strval(0); //我去过

            /* 查询去向的数量 */
            $whereUserLikeCount['shop_id'] = array('IN', $shop_id);
            $whereUserLikeCount['user.status'] = array('EQ', 1);
            $whereUserLikeCount['user.display'] = array('EQ', 1);
            $listUserLikeCount = M('ShopUserWent')
                ->alias('shop_user_went')
                ->where($whereUserLikeCount)
                ->join('__USER__ user on shop_user_went.user_id = user.id')
                ->count();
            $data['want_count'] = $listUserLikeCount;
        }

        $return_data['data'] = arr_content_replace($data);
        /* 读取json */
        return $return_data;
    }

    /* 自动验证和自动完成函数 */
}