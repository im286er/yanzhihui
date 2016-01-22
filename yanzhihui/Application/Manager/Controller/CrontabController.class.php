<?php
namespace Manager\Controller;

use Think\Controller;

define('SNAPSHOTZANBT','07:00:00');
define('SNAPSHOTZANET','06:59:59');

class CrontabController extends Controller {


	/**
	* 每小时影像报表
	*@params $d Y-m-d
	*@params $h 0-23   0表示 00:00:00-00:59:59
	*@return null
	*
	*/
    public function topic_snapshot_hours(){
		
		  $d = I('get.d');
		  $h = I('get.h');
		//搜索日期及小时处理
		if(isset($d) &&$d!=''){
			$eventdate= $d;
		}else{
			$eventdate = date('Y-m-d');
		}

		if(isset($h)&&$h!=''){
			$h = $h;
		}else{
			$h = date('H');
			if($h==0){
				$eventdate = date('Y-m-d',time()-86400);
				$h = 23;
			}else{
				$h = $h-1;

			}
		}

		//时间范围生成
		if($h<10){
			$h1 = '0'.$h;
		}else{
			$h1 = $h;
		}

		$bt = strtotime($eventdate.' '.$h1.':00:00');
		$et = strtotime($eventdate.' '.$h1.':59:59');

		
		
		$where['create_time'] = array('BETWEEN', array($bt,$et));
		
		$where['display'] = array('EQ', 1);
		/* 查询数据 */
		$nums = M('Topic')->where($where)->count('id');

		//echo M('Topic')->_sql();


		$where['display'] = array('EQ', 0);
		/* 查询数据 */
		$nums_del = M('Topic')->where($where)->count('id');
		//echo M('Topic')->_sql();

		$data["eventdate"] = $eventdate;
		$data["h"] = $h;
		$data["nums"] = $nums>0?$nums:0;
		$data["nums_del"] = $nums_del>0?$nums_del:0;
		$data["create_time"] = date("Y-m-d H:i:s",time());
		M('TopicSnapshotHours')->data($data)->add();
		//echo M('TopicSnapshotHours')->_sql();
		
	}

	/**
	* 每天影像报表
	*@params $d Y-m-d
	*@return null
	*
	*/

	public function topic_snapshot_day(){

       $d = I('get.d');

		if(isset($d) &&$d!=''){
			$eventdate= $d;
		}else{
			$eventdate = date('Y-m-d',time()-86400);
		}

		$bt = strtotime($eventdate.' 00:00:00');
		$et = strtotime($eventdate.' 23:59:59');

		
		
		$where['create_time'] = array('BETWEEN', array($bt,$et));

		$where['display'] = array('EQ', 1);
		/* 查询数据 */
		$nums = M('Topic')->where($where)->count('id');

		//echo M('Topic')->_sql();


		$where['display'] = array('EQ', 0);
		/* 查询数据 */
		$nums_del = M('Topic')->where($where)->count('id');
		//echo M('Topic')->_sql();
		
		$data["eventdate"] = $eventdate;
		$data["nums"] = $nums>0?$nums:0;
		$data["nums_del"] = $nums_del>0?$nums_del:0;
		$data["create_time"] = date("Y-m-d H:i:s",time());
		M('TopicSnapshotDay')->data($data)->add();
		//echo M('TopicSnapshotDay')->_sql();

	}


	/**
	* 每天用户获取点赞数统计
	* 每天0点30分运行
	*@params $d Y-m-d
	*@return null
	*
	*/
	public function topic_snapshot_zan_user(){
		$d = I('get.d');

		if(isset($d) &&$d!=''){
			$eventdate= $d;
		}else{
			$eventdate = date('Y-m-d',time()-86400);
		}

		$bt = strtotime($eventdate.' 00:00:00');
		$et = strtotime($eventdate.' 23:59:59');
		
		$field = 'topic_like.topic_id,user.id as user_id,user.city';
		$where['topic_like.create_time'] = array('BETWEEN', array($bt,$et));
		$where['topic.display'] = array('EQ', 1);

		$data = M('TopicLike')	
			->alias('topic_like')
			->join('INNER JOIN __TOPIC__ topic on topic.id = topic_like.topic_id')
			->join('INNER JOIN __USER__ user on topic.user_id = user.id')
			->field($field)
			->where($where)
			->select();
		
		$tmpArr = array();
		if(count($data)!=0){
			foreach($data as $k=>$v){
				$tmpArr[$v["user_id"]]["count"] = $tmpArr[$v["user_id"]]["count"]+1;
				$tmpArr[$v["user_id"]]["city"] = $v["city"];
				$tmpArr[$v["user_id"]]["user_id"] = $v["user_id"];
			}
		}
        
        if(count($tmpArr)!=0){

			foreach($tmpArr as $k=>$v){
				$data["user_id"] = $v["user_id"];
				$data["nums"] = $v["count"];
				$data["city"] = $v["city"];
				$data["eventdate"] = $eventdate;
				$data["create_time"] = date("Y-m-d H:i:s",time());
				M('TopicSnapshotDayZanUsers')->data($data)->add();

			}
		}


	}


	/**
	* 每天获取用户发布图片及图片比例数统计
	* 统计时段: 7:00 - 次日6:59:59
	* 每天 7点30分运行
	*@params $d Y-m-d
	*@return null
	*
	*/
	public function topic_snapshot_day_add(){
		$d = I('get.d');

		if(isset($d) &&$d!=''){
			$eventdate= $d;
		}else{
			$eventdate = date('Y-m-d',time()-86400);
		}
        
		$ed = date('Y-m-d',strtotime($eventdate)+86400);
		$bt = strtotime($eventdate.' '.SNAPSHOTZANBT);
		$et = strtotime($ed.' '.SNAPSHOTZANET);
		
		$field = 'topic.id,user.id as user_id';
		$where['topic.create_time'] = array('BETWEEN', array($bt,$et));
		$where['topic.display'] = array('EQ', 1);

		$data = M('Topic')	
			->alias('topic')
			->join('INNER JOIN __USER__ user on topic.user_id = user.id')
			->field($field)
			->where($where)
			->select();
		
		$tmpArr = array();
		$topic_nums = 0;
		$level_nums = array();
		$level_type = array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0,'6'=>0,'7'=>0,'8'=>0,'9'=>0,'10'=>0,'11'=>0);
		if(count($data)!=0){
			foreach($data as $k=>$v){
				$tmpArr[$v["user_id"]]["count"] = $tmpArr[$v["user_id"]]["count"]+1;
				$tmpArr[$v["user_id"]]["user_id"] = $v["user_id"];
				$topic_nums = $topic_nums+1;
			}
		}     
	    $users_nums =  count($tmpArr)>0?count($tmpArr):0;

		//插入每天统计数据
		$data["users_nums"] = $users_nums;
		$data["topic_nums"] = $topic_nums;
		$data["eventdate"] = $eventdate;
        $data["create_time"] = date("Y-m-d H:i:s",time());
		M('TopicSnapshotDayAdd')->data($data)->add();
		unset($data);
        
        if(count($tmpArr)!=0){

			foreach($tmpArr as $k=>$v){
				
				switch($v["count"]){
					case"1":
							$level_type[1] = $level_type[1]+1;
					break;
					case"2":
							$level_type[2] = $level_type[2]+1;
					break;
					case"3":
							$level_type[3] = $level_type[3]+1;
					break;
					case"4":
							$level_type[4] = $level_type[4]+1;
					break;
					case"5":
							$level_type[5] = $level_type[5]+1;
					break;
					case"6":
							$level_type[6] = $level_type[6]+1;
					break;
					case"7":
							$level_type[7] = $level_type[7]+1;
					break;
					case"8":
							$level_type[8] = $level_type[8]+1;
					break;
					case"9":
							$level_type[9] = $level_type[9]+1;
					break;
					case"10":
							$level_type[10] = $level_type[10]+1;
					break;
					default:
							$level_type[11] = $level_type[11]+1;
					break;
				}				

			}

			if(count($level_type)!=0){

				foreach($level_type as $k=>$v){

				$data["level_type"] = $k;
				$data["users_nums"] = $v;
				$data["total_users_nums"] = $users_nums;
				$data["eventdate"] = $eventdate;
				$data["create_time"] = date("Y-m-d H:i:s",time());
				M('TopicSnapshotDayAddLevel')->data($data)->add();
				unset($data);
				}
			}
		}
	}


	/**
	* 每天图片点赞数及档次比例分布统计
	* 每天7点10分运行
	*@params $d Y-m-d
	*@return null
	*
	*/
	public function topic_snapshot_zan_level(){
		$d = I('get.d');

		if(isset($d) &&$d!=''){
			$eventdate= $d;
		}else{
			$eventdate = date('Y-m-d',time()-86400);
		}
        
		$ed = date('Y-m-d',strtotime($eventdate)+86400);
		$bt = strtotime($eventdate.' '.SNAPSHOTZANBT);
		$et = strtotime($ed.' '.SNAPSHOTZANET);

		
		$field = 'id,create_time';
		$where['create_time'] = array('BETWEEN', array($bt,$et));
		$where['display'] = array('EQ', 1);
		
		//获取时间段内发布的图片
		$data = M('Topic')	
			->alias('topic')
			->field($field)
			->where($where)
			->select();
		
		$tmpArr = array(); //保存 topic_id;
		$total_zan = 0;
		$total_zan_2hours = 0;
		$level_type = array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0,'6'=>0,'7'=>0,'8'=>0,'9'=>0,'10'=>0,'11'=>0);
		if(count($data)!=0){

			//根据图片id获取点赞数统计
			foreach($data as $k=>$v){
				
				$wherelike["topic_id"] = array('EQ',$v["id"]);
				$wherelike["create_time"] = array('BETWEEN', array($bt,$et));
				//时间段内总点赞数
				$total = M('TopicLike')->where($wherelike)->count();
				$total=$total>0?$total:0;
				//累计图片总点击数
				$total_zan = $total_zan+$total;
				//2小时内点击数
                $ett = $v["create_time"]+7200;
				if($ett<$et){
					$ett = $ett;
				}else{
					$ett = $et;
				}
				$wherelike["create_time"] = array('BETWEEN', array($bt,$ett));
				$total_2hour = M('TopicLike')->where($wherelike)->count();
				$total_2hour = $total_2hour>0?$total_2hour:0;
				//累计2小时总点击数据
				$total_zan_2hours = $total_zan_2hours+$total_2hour;

				$total_others = $total-$total_2hour;	
				
				$tmpArr[] = $v["id"];

				if($total>=0&&$total<=10){
					$level_type[1] = $level_type[1]+1;
				}elseif($total>=11&&$total<=20){
					$level_type[2] = $level_type[2]+1;
				}elseif($total>=21&&$total<=30){
					$level_type[3] = $level_type[3]+1;
				}elseif($total>=31&&$total<=40){
					$level_type[4] = $level_type[4]+1;
				}elseif($total>=41&&$total<=50){
					$level_type[5] = $level_type[5]+1;
				}elseif($total>=51&&$total<=60){
					$level_type[6] = $level_type[6]+1;
				}elseif($total>=61&&$total<=70){
					$level_type[7] = $level_type[7]+1;
				}elseif($total>=71&&$total<=80){
					$level_type[8] = $level_type[8]+1;
				}elseif($total>=81&&$total<=90){
					$level_type[9] = $level_type[9]+1;
				}elseif($total>=91&&$total<=100){
					$level_type[10] = $level_type[10]+1;
				}else{
					$level_type[11] = $level_type[11]+1;
				}
				
				//插入图片细节表 
				$data1["eventdate"] = $eventdate;
				$data1["topic_id"] = $v["id"];
				$data1["like_nums"] = $total;
				$data1["like_nums_2hours"] = $total_2hour;
				$data1["like_nums_others"] = $total_others;
				$data1["create_time"] = date("Y-m-d H:i:s",time());
				M('TopicSnapshotDayZanDetail')->data($data1)->add();
				unset($data1);

			}


			//插入分布数据
			
			if(count($level_type)!=0){

				foreach($level_type as $k=>$v){

				$data1["level_type"] = $k;
				$data1["nums"] = $v;
				$data1["total_nums"] = count($tmpArr);
				$data1["eventdate"] = $eventdate;
				$data1["create_time"] = date("Y-m-d H:i:s",time());
				M('TopicSnapshotDayZanLevel')->data($data1)->add();
				unset($data1);
				}
			}
            
			$total_users_nums = 0;
			$where = array();
			//检索参与新图片点赞人数
			if(count($tmpArr)!=0){
				$str = implode(',',$tmpArr);
				$filed = " distinct(user_id)";
				$where['create_time'] = array('BETWEEN', array($bt,$et));
				$where['topic_id']= array('IN',$str);
				$data2 = M('TopicLike')->field($filed)->where($where)->select();

				$total_users_nums = count($data2);
			}
            
			$data1 = array();
			$data1["eventdate"] = $eventdate;
			$data1["like_nums"] = $total_zan;
			$data1["user_nums"] = $total_users_nums;
			$data1["like_nums_2hours"] = $total_zan_2hours;
			$data1["like_nums_others"] = $total_zan-$total_zan_2hours;
			$data1["create_time"] = date("Y-m-d H:i:s",time());
			M('TopicSnapshotDayZan')->data($data1)->add();
			unset($data1);
		}





	}



	/**
	* 每周日24点影像当前颜币数及档次比例分布统计
	* 每周日24点
	*@params $d Y-m-d
	*@return null
	*
	*/
	public function yanbi_snapshot_level(){


		$eventdate = date("Y-m-d",time()-1200);
				
		$field = 'id,like_count';	
		$where['display'] = array('EQ', 1);
		
		//获取时间段内发布的图片
		$data = M('User')	
			->alias('user')
			->field($field)
			->where($where)
			->select();		
		
		$total_yanbi = 0;
		$total_users = 0;
		$level_type = array('1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0,'6'=>0,'7'=>0,'8'=>0,'9'=>0,'10'=>0,'11'=>0);
		if(count($data)!=0){
			$total_users = count($data);
			//根据图片id获取点赞数统计
			foreach($data as $k=>$v){
				
				$total  =$v["like_count"];
				$total_yanbi = $total_yanbi+$total;
				

				if($total==0){
					$level_type[1] = $level_type[1]+1;
				}elseif($total>=1&&$total<=200){
					$level_type[2] = $level_type[2]+1;
				}elseif($total>=201&&$total<=500){
					$level_type[3] = $level_type[3]+1;
				}elseif($total>=501&&$total<=1000){
					$level_type[4] = $level_type[4]+1;
				}elseif($total>=1001&&$total<=2000){
					$level_type[5] = $level_type[5]+1;
				}elseif($total>=2001&&$total<=5000){
					$level_type[6] = $level_type[6]+1;
				}elseif($total>=5001&&$total<=10000){
					$level_type[7] = $level_type[7]+1;
				}elseif($total>=10001&&$total<=20000){
					$level_type[8] = $level_type[8]+1;
				}elseif($total>=20001&&$total<=50000){
					$level_type[9] = $level_type[9]+1;
				}elseif($total>=50001&&$total<=100000){
					$level_type[10] = $level_type[10]+1;
				}else{
					$level_type[11] = $level_type[11]+1;
				}		
				

			}

			//插入分布数据
			
			if(count($level_type)!=0){

				foreach($level_type as $k=>$v){

				$data1["level_type"] = $k;
				$data1["nums"] = $v;
				$data1["total_nums"] = $total_users;
				$data1["eventdate"] = $eventdate;
				$data1["create_time"] = date("Y-m-d H:i:s",time());
				M('YanbiSnapshotWeekLevel')->data($data1)->add();
				unset($data1);
				}
			}
            
			
		}
	}


	/**
	* 每天用户影像报表
	*@params $d Y-m-d
	*@return null
	*
	*/

	public function user_snapshot_day(){

       $d = I('get.d');

		if(isset($d) &&$d!=''){
			$eventdate= $d;
		}else{
			$eventdate = date('Y-m-d',time()-86400);
		}

		$bt = strtotime($eventdate.' 00:00:00');
		$et = strtotime($eventdate.' 23:59:59');		
		
		$where['create_time'] = array('BETWEEN', array($bt,$et));
		$where['display'] = array('EQ', 1);
		/* 当天新增用户数 */
		$total_users = M('User')->where($where)->count('id');
		//echo M('User')->_sql();
        
		$subQuery = M('User')->field('id')->where($where)->buildSql(); 
		//$wheretopic["user_id"]=array('IN',$subQuery);
		//$wheretopic['create_time'] = array('BETWEEN', array($bt,$et));
		$add_topic_users = M('Topic')->where(' user_id in ('.$subQuery.') and create_time between '.$bt.' and '.$et.' and display=1 ' )->count('DISTINCT user_id');
		
		//echo M()->_sql();
		
		$data["eventdate"] = $eventdate;
		$data["add_topic_users"] = $add_topic_users>0?$add_topic_users:0;
		$data["total_users"] = $total_users>0?$total_users:0;
		$data["create_time"] = date("Y-m-d H:i:s",time());
		M('UserSnapshotDay')->data($data)->add();
		//echo M('UserSnapshotDay')->_sql();

	}
    
}