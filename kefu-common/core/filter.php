<?php
/**
 *      [Gome Wap!] (C)2013-2023 Gome Inc.
 *       desc:过滤器控制器，控制接口和IP访问的次数限制
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Filter.php$
 */
class Filter
{
    protected $input;
    protected $m_db;
    protected $interface;//调用接口的名称
	public function __construct($interface=false)
	{
		$this->input = Input::get_instance();
        $this->m_db  = RedisCache::get_instance(0);
	}

    /**
     *用来检测用户端IP的请求信息限制流量入口【流量控制】
     *****/
    public function trafficFilter()
    {
        $ip = $this->input->ip_address();
        $date_ymd = date("Y-m-d");
        $log_file = 'userIpCheck';
        $time = time();
        $expire_time = 3600*24;//过期时间
        $per_count = 3;//每秒限制访问最高次数
        $per_day = 10;//限制访问的最高次数
        $msg = '';
        $ip_key = $ip.'info';
        $ip_lock_key = $ip.'lock';
        $request_info = $this->m_db->get($ip_key);
        $is_lock = $this->m_db->get($ip_lock_key);
        if ($is_lock) {
            $msg=$ip.'被锁定,调用次数超限lock';
            $this->errReturn($request_info,$log_file,$request_info,1,$msg);
        }

        if (!empty($request_info)) {
            if( $request_info['click']>$per_count || $request_info['clickcount']>$per_day)
            {
                $msg=$ip.'被锁定,调用次数超限';
                $this->errReturn($request_info,$log_file,$request_info,1,$msg);
            }
            //统计一天的总量
            if ($request_info['date_day'] == $date_ymd) {
                $request_info['clickcount'] += 1;
            } else {
                $request_info['clickcount'] = 2;
                $request_info['date_day'] = $date_ymd;
            }
            if($request_info['clickcount']>$per_day){
                $this->m_db->set($ip_lock_key, 1,$expire_time );
            }
            //访问的总次数
            $request_info['clickall'] += 1;
            //统计每秒钟的点击量
            $cle = $time - $request_info['adddate'];
            $s = floor(($cle%(3600*24))%60);
            if ( $s<= 1) {
                $request_info['click'] = $request_info['click'] + 1;
            } else {
                $this->m_db->set($ip_lock_key, 1,$expire_time );
                $request_info['adddate'] = $time;
            }
            $check_ip = json_encode($request_info);
            $this->m_db->set($ip_key, $check_ip,$expire_time );
            $this->printJson('',0,'succ');
        }
        $data = array(
            'ip' => $ip,
            'adddate' => $time,
            'click'=>1,
            'clickcount'=>2,
            'date_day'=>$date_ymd,
        );
        $check_ip = json_encode($data);
        $res = $this->m_db->set($ip_key,$check_ip,$expire_time );
        if(!$res){
            $this->errReturn($check_ip,$log_file,$data,1,'redis add failed!');
        }
        $this->printJson('',0,'succ');
    }

    /**
     * 输出json数据
     * @param mixed $data 主数据
     * @param int $error error code
     * @param string $msg error message
     */
    public function printJson ($data = null, $error = 0, $msg = '', $exit = true) {
        echo json_encode(array('data'=>$data, 'error'=>$error, 'msg'=>$msg));
        if($exit){
            exit();
        }
    }
    /**
     *记录日志，返回错误信息
     * @param string $msg 错误日志信息
     * @param string $log_file 错误日志文件
     * @param string $data 返回数据信息
     * @param string $code 返回错误码
     * @param string $return 返回错误提示语
     * @return json
     */
    public function errReturn($msg,$log_file,$data,$code,$return){
        Log2File($msg,$log_file);
        $this->printJson($data,$code,$return);
    }

}