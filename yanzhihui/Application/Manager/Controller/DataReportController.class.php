<?php
namespace Manager\Controller;

use Think\Controller;

class DataReportController extends BaseController {
    /**
     * 列表 hours
     */
    public function topic_snapshot_hours() {

		$name ='TopicSnapshotHours';
        /* 定义变量 */
        $getH = I('get.h');

		$getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time());
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time());
		}

        /* 查询条件 */
        $field = 'id,eventdate,h,nums,nums_del';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));  
		if ($getH) {
            $where['h'] = array('EQ', $getH);
        }
        
        /* 查询排序 */
        $order = 'id desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();   

        $this->assign('list', $list);
        $this->display();
    }


	/**
     * 列表 hours
     */
    public function topic_snapshot_day() {

		$name ='TopicSnapshotDay';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time()-86400);
		}


        /* 查询条件 */
        $field = 'id,eventdate,nums,nums_del';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'id desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();   

        $this->assign('list', $list);
        $this->display();
    }   

	
	/**
     * 每天新增图片分布比例
     */
    public function topic_snapshot_day_add_level() {

		$name ='TopicSnapshotDayAddLevel';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time()-86400);
		}


        /* 查询条件 */
        $field = 'id,eventdate,level_type,users_nums,total_users_nums';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'eventdate desc,users_nums desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();   

		foreach($list as $k => $v){
            
			$list[$k]['present'] = round(($list[$k]['users_nums']/$list[$k]['total_users_nums']) * 100,2);
			switch($list[$k]['level_type']){
				case"1":
						$list[$k]['level_type'] = '发布1张图片';
				break;
				case"2":
						$list[$k]['level_type'] = '发布2张图片';
				break;
				case"3":
						$list[$k]['level_type'] = '发布3张图片';
				break;
				case"4":
						$list[$k]['level_type'] = '发布4张图片';
				break;
				case"5":
						$list[$k]['level_type'] = '发布5张图片';
				break;
				case"6":
						$list[$k]['level_type'] = '发布6张图片';
				break;
				case"7":
						$list[$k]['level_type'] = '发布7张图片';
				break;
				case"8":
						$list[$k]['level_type'] = '发布8张图片';
				break;
				case"9":
						$list[$k]['level_type'] = '发布9张图片';
				break;
				case"10":
						$list[$k]['level_type'] = '发布10张图片';
				break;
				case"11":
						$list[$k]['level_type'] = '发布11张及以上图片';
				break;
			}
        }

        $this->assign('list', $list);
        $this->display();
    }   

	
	/**
     * 每天新增图片点赞分布比例
     */
    public function topic_snapshot_day_zan_level() {

		$name ='TopicSnapshotDayZanLevel';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time()-86400);
		}


        /* 查询条件 */
        $field = 'id,eventdate,level_type,nums,total_nums';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'eventdate desc,nums desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();   

		foreach($list as $k => $v){
            
			$list[$k]['present'] = round(($list[$k]['nums']/$list[$k]['total_nums']) * 100,2);
			switch($list[$k]['level_type']){
				case"1":
						$list[$k]['level_type'] = '0-10个颜币';
				break;
				case"2":
						$list[$k]['level_type'] = '11-20个颜币';
				break;
				case"3":
						$list[$k]['level_type'] = '21-30个颜币';
				break;
				case"4":
						$list[$k]['level_type'] = '31-40个颜币';
				break;
				case"5":
						$list[$k]['level_type'] = '41-50个颜币';
				break;
				case"6":
						$list[$k]['level_type'] = '51-60个颜币';
				break;
				case"7":
						$list[$k]['level_type'] = '61-70个颜币';
				break;
				case"8":
						$list[$k]['level_type'] = '71-80个颜币';
				break;
				case"9":
						$list[$k]['level_type'] = '81-90个颜币';
				break;
				case"10":
						$list[$k]['level_type'] = '91-100个颜币';
				break;
				case"11":
						$list[$k]['level_type'] = '大于101个颜币';
				break;
			}
        }

        $this->assign('list', $list);
        $this->display();
    }   

	/**
     * 用户点赞统计报表
     */
    public function topic_snapshot_day_zan_users() {

		$name ='TopicSnapshotDayZanUsers';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time()-86400);
		}


        /* 查询条件 */
        $field = 'topic_snapshot_day_zan_users.id,topic_snapshot_day_zan_users.eventdate,topic_snapshot_day_zan_users.user_id,topic_snapshot_day_zan_users.nums,topic_snapshot_day_zan_users.city,user.upfile_head,user.nick_name';
        $where['topic_snapshot_day_zan_users.display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['topic_snapshot_day_zan_users.eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'topic_snapshot_day_zan_users.nums desc';
        /* 分页查询 */
        $count = M($name)
			->alias('topic_snapshot_day_zan_users')
			->join('INNER JOIN __USER__ user on topic_snapshot_day_zan_users.user_id = user.id')
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
			->alias('topic_snapshot_day_zan_users')
            ->field($field)
            ->where($where)
			->join('INNER JOIN __USER__ user on topic_snapshot_day_zan_users.user_id = user.id')
            ->order($order)
            ->limit($limit)
            ->select();   

		 foreach($list as $k => $v){
            if ($v['upfile_head'] && !strstr($v['upfile_head'], 'http://')) {
                $list[$k]['upfile_head'] = '/Uploads/Images/User/' . $v['upfile_head'];
            }
        }

        $this->assign('list', $list);
        $this->display();
    }   

	
	/**
     * 每天用户点赞统计报表
     */
    public function topic_snapshot_day_zan() {

		$name ='TopicSnapshotDayZan';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time()-86400);
		}


        /* 查询条件 */
        $field = 'id,eventdate,like_nums,user_nums,like_nums_2hours,like_nums_others';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'eventdate desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();  
        $this->assign('list', $list);
        $this->display();
    }   


	/**
     * 每周颜币用户分布比例
     */
    public function yanbi_snapshot_week_level() {

		$name ='YanbiSnapshotWeekLevel';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time());
		}


        /* 查询条件 */
        $field = 'id,eventdate,level_type,nums,total_nums';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'eventdate desc,nums desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();   

		foreach($list as $k => $v){
            
			$list[$k]['present'] = round(($list[$k]['nums']/$list[$k]['total_nums']) * 100,2);
			switch($list[$k]['level_type']){
				case"1":
						$list[$k]['level_type'] = '0个颜币';
				break;
				case"2":
						$list[$k]['level_type'] = '1-200个颜币';
				break;
				case"3":
						$list[$k]['level_type'] = '201-500个颜币';
				break;
				case"4":
						$list[$k]['level_type'] = '501-1000个颜币';
				break;
				case"5":
						$list[$k]['level_type'] = '1001-2000个颜币';
				break;
				case"6":
						$list[$k]['level_type'] = '2001-5000个颜币';
				break;
				case"7":
						$list[$k]['level_type'] = '5001-10000个颜币';
				break;
				case"8":
						$list[$k]['level_type'] = '10001-20000个颜币';
				break;
				case"9":
						$list[$k]['level_type'] = '20001-50000个颜币';
				break;
				case"10":
						$list[$k]['level_type'] = '50001-100000个颜币';
				break;
				case"11":
						$list[$k]['level_type'] = '100000个颜币以上';
				break;
			}
        }

        $this->assign('list', $list);
        $this->display();
    }   


	/**
     * 每天新用户及新用户发布图片统计
     */
    public function user_snapshot_day() {

		$name ='UserSnapshotDay';
        /* 定义变量 */
        $getStartdate = I('get.startdate');
		$getEnddate = I('get.enddate');

		if(empty($getStartdate)){
			$getStartdate = date("Y-m-d",time()-86400);
		}

		if(empty($getEnddate)){
			$getEnddate = date("Y-m-d",time()-86400);
		}


        /* 查询条件 */
        $field = 'id,eventdate,add_topic_users,total_users';
        $where['display'] = array('EQ', 1);
        /* 搜索条件 */
        
         $where['eventdate'] = array('BETWEEN', array($getStartdate,$getEnddate));      
	
        
        /* 查询排序 */
        $order = 'eventdate desc';
        /* 分页查询 */
        $count = M($name)
            ->where($where)
            ->count();
        $limit = $this->Page($count);
        /* 查询列表 */
        $list = M($name)
            ->field($field)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->select();   

		foreach($list as $k => $v){
            
			$list[$k]['present'] = round(($list[$k]['add_topic_users']/$list[$k]['total_users']) * 100,2);
			
        }

        $this->assign('list', $list);
        $this->display();
    }   


}