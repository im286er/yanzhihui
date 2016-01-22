<?php
namespace Api\Model;

class TopicLogModel extends CommonModel {
    

    /**
     * 添加上榜记录
     */
    public function do_add(){
        $bool = false;      // 默认不能插入
        /* 接收参数 */
        $model = M('TopicLog');
        $user_id = I('post.user_id');
        $type = I('post.type');
        $number = I('post.number');
        $name = I('post.name');
        $user_id = empty($user_id) ? 0 : $user_id;

        /* 查询条件 */
        $where['user_id'] = array('EQ',1);
        //var_dump($where);exit;
        $where['type'] = array('EQ',$type);
        /* 排序条件 */
        $order = 'create_time desc';
        /* 查出相同榜单相同用户的最新一次记录 */
        $timeArr = $model->field('create_time')->where($where)->order($order)->limit(1)->select();
        $create_time = $timeArr[0]['create_time'];
        /* 如果不是空，要与当前时间比较 */
        if(!empty($create_time)){
            $log_time = date("Y-m-d H:i:s",$create_time);
            $now_time = date("Y-m-d H:i:s",time());
            $hour = (strtotime($now_time)-strtotime($log_time))/(60*60);
            $hour = intval($hour);
            /* 如果大于24小时，允许插入 */
            if($hour > 24){
                $bool = true;
            }
        /* 如果查不出来，说明表中没有记录，允许插入 */
        }else{
            $bool = true;
        }
        $result = 0;
        /* 插入操作 */
        if($bool){
            $data['user_id'] = $user_id;
            $data['type'] = $type;
            $data['number'] = $number;
            $data['name'] = $name;
            $data['create_time'] = NOW_TIME;
            $result = $model->add($data);
        }
        /* 如果记录有插入 */
        if($result){
            $criteria['id'] = array('EQ',$user_id);
            $arr = M('User')->where($criteria)->select();
            $info = $arr[0];
            /* 上榜次数+1 */
            $info['top_times'] = $info['top_times']+1;
            /* 如果这次排名比原最好考前，修改最好名次 */
            if(intval($info['top_best']) == 0 || $info['top_best'] > $number){
                $info['top_best'] = $number;
            }
            $qq = M('User')->where($criteria)->save($info);
            $arr = M('User')->where($criteria)->select();
        }
        return $result;
    }




    /**
     * 获取我的上榜记录
     */
    public function do_list(){
        $user_id = I('get.user_id');
        $model = M('TopicLog');
        $where['user_id'] = array('EQ',$user_id);
        $order = 'id desc,type desc';
        $list = $model->where($where)->order($order)->select();
        $_list = array();
        if(!empty($list)){
            /* 遍历每个数组 */
            foreach ($list as $v) {
                $v['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
                $_list[] = $v;
            }
        }
        /* 读取json */
        $_list = empty($_list) ? array() : $_list;
        $jsonInfo['list'] = arr_content_replace($_list);
        return $jsonInfo;

    }





}