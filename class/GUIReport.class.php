<?php
/**
 * GUIReport - prepare data for GUI
 * User: Samuel Zhang
 * Date: 2016-08-12
 */
class GUIReport extends ServerReport
{
	protected $db_conn;
	protected $report;
	
	public function __construct(){
		$this->db_conn = new PdoConnection();
		$this->server_report = new ServerReport();
	}
	
	public function get_server_specs($sid){
		$response = array();
		$response['cols'] = array(
            array(
                'label' => 'Type',
                'type' => 'string',
            ),
            array(
                'label' => 'Value',
                'type' => 'string',
            ),
        );
		
		$data = $this->server_report->getRemoteLoad($sid,true);
		
		$response['rows'][]['c'] = array(array('v' =>'CPU Model'),array('v' =>$data['cpu_info']['model']),);
		$response['rows'][]['c'] = array(array('v' =>'CPU Cores'),array('v' =>$data['cpu_info']['cores']),);
		$response['rows'][]['c'] = array(array('v' =>'CPU Speed'),array('v' =>$data['cpu_info']['frequency']),);
		$response['rows'][]['c'] = array(array('v' =>'CPU Cache'),array('v' =>$data['cpu_info']['cache']),);
		$response['rows'][]['c'] = array(array('v' =>'CPU Bogomips'),array('v' =>$data['cpu_info']['bogomips']),);
		$response['rows'][]['c'] = array(array('v' =>'OS Version'),array('v' =>$data['os_info']['os']),);
		$response['rows'][]['c'] = array(array('v' =>'OS Kernel'),array('v' =>$data['os_info']['kernel']),);
		$response['rows'][]['c'] = array(array('v' =>'Last Boot'),array('v' => $data['os_info']['reboot']),);
		$response['rows'][]['c'] = array(array('v' =>'Total PIDs'),array('v' => $data['load_info']['total_pids']),);
		$response['rows'][]['c'] = array(array('v' =>'Memory Total'),array('v' =>Tool::humanSize($data['mem_info']['total'],'K')),);
		$response['rows'][]['c'] = array(array('v' =>'Memory Usage'),array('v' =>(Tool::humanSize($data['mem_info']['used'],'K').' ('.$data['mem_info']['use_pert'].'%)')),);
		$response['rows'][]['c'] = array(array('v' =>'Disk Total'),array('v' =>Tool::humanSize($data['disk_info']['total'],'K')),);
		$response['rows'][]['c'] = array(array('v' =>'Disk Usage'),array('v' =>(Tool::humanSize($data['disk_info']['used'],'K').' ('.$data['disk_info']['use_pert'].'%)')),);
		$response['rows'][]['c'] = array(array('v' =>'Disk Read'),array('v' => $data['load_info']['disk_rkbs'].' KB/s'),);
		$response['rows'][]['c'] = array(array('v' =>'Disk Write'),array('v' =>  $data['load_info']['disk_wkbs'].' KB/s'),);
		return $response;
	}
	
	public function get_load_trend($sid,$date=false){
		$response = array();
		$response['cols'] = array(
            array(
                'label' => 'Datetime',
                'type' => 'datetime',
            ),
            array(
                'label' => '1 Min Avg',
                'type' => 'number',
            ),
			array(
                'label' => '5 Min Avg',
                'type' => 'number',
            ),
			array(
                'label' => '15 Min Avg',
                'type' => 'number',
            ),
        );
		
		$server_id = $this->db_conn->quote($sid);
		if($date){
			$date_start = $this->db_conn->quote($date.' 00:00:00');
			$date_end = $this->db_conn->quote($date.' 23:59:59');
			$sql = "SELECT ServerId,load_1min,load_5min,load_15min,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id AND CreatedDT >= $date_start AND CreatedDT <= $date_end ORDER BY CreatedDT DESC LIMIT 1440;";
			
		}else{
			$sql = "SELECT ServerId,load_1min,load_5min,load_15min,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id ORDER BY CreatedDT DESC LIMIT 1440;";
		}
		$result = $this->db_conn->fetchAll($sql);
		foreach ($result as $row){
			$source = new DateTime($row['CreatedDT']);
			$gtime = 'Date('.$source->format('Y,n,j,G,i,s').')';
			$load1 = round($row['load_1min'],2);
			$load2 = round($row['load_5min'],2);
			$load3 = round($row['load_15min'],2);
			$response['rows'][]['c'] = array(
				array('v' => $gtime ),
				array('v' => $load1 ),
				array('v' => $load2 ),
				array('v' => $load3 ),
			);
		}
		return $response;
	}
	
	public function get_pid_trend($sid){
	
	}
	
	public function get_mem_use_trend($sid,$date=false){
		$response = array();
		$response['cols'] = array(
            array(
                'label' => 'Datetime',
                'type' => 'datetime',
            ),
            array(
                'label' => 'Memory Usage',
                'type' => 'number',
            ),
        );
		$server_id = $this->db_conn->quote($sid);
		if($date){
			$date_start = $this->db_conn->quote($date.' 00:00:00');
			$date_end = $this->db_conn->quote($date.' 23:59:59');
			$sql = "SELECT ServerId,mem_use_pert,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id AND CreatedDT >= $date_start AND CreatedDT <= $date_end ORDER BY CreatedDT DESC LIMIT 1440;";
			
		}else{
			$sql = "SELECT ServerId,mem_use_pert,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id ORDER BY CreatedDT DESC LIMIT 1440;";
		}
		$result = $this->db_conn->fetchAll($sql);
		foreach ($result as $row){
			$source = new DateTime($row['CreatedDT']);
			$gtime = 'Date('.$source->format('Y,n,j,G,i,s').')';
			$mem_use = round($row['mem_use_pert'],2);
			$response['rows'][]['c'] = array(
				array('v' => $gtime ),
				array('v' => $mem_use ),
			);
		}
		return $response;
	}
	
	public function get_disk_tps_trend($sid,$date=false){
		$response = array();
		$response['cols'] = array(
            array(
                'label' => 'Datetime',
                'type' => 'datetime',
            ),
            array(
                'label' => 'Disk TPS',
                'type' => 'number',
            ),
        );
		$server_id = $this->db_conn->quote($sid);
		if($date){
			$date_start = $this->db_conn->quote($date.' 00:00:00');
			$date_end = $this->db_conn->quote($date.' 23:59:59');
			$sql = "SELECT ServerId,disk_tps,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id AND CreatedDT >= $date_start AND CreatedDT <= $date_end ORDER BY CreatedDT DESC LIMIT 1440;";
			
		}else{
			$sql = "SELECT ServerId,disk_tps,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id ORDER BY CreatedDT DESC LIMIT 1440;";
		}
		$result = $this->db_conn->fetchAll($sql);
		foreach ($result as $row){
			$source = new DateTime($row['CreatedDT']);
			$gtime = 'Date('.$source->format('Y,n,j,G,i,s').')';
			$disk_tps = round($row['disk_tps'],2);
			$response['rows'][]['c'] = array(
				array('v' => $gtime ),
				array('v' => $disk_tps ),
			);
		}
		return $response;
	}
	
	public function get_php_pid_trend($sid,$date=false){
		$response = array();
		$response['cols'] = array(
            array(
                'label' => 'Datetime',
                'type' => 'datetime',
            ),
            array(
                'label' => 'PHP Pids',
                'type' => 'number',
            ),
        );
		$server_id = $this->db_conn->quote($sid);
		if($date){
			$date_start = $this->db_conn->quote($date.' 00:00:00');
			$date_end = $this->db_conn->quote($date.' 23:59:59');
			$sql = "SELECT ServerId,php_pids,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id AND CreatedDT >= $date_start AND CreatedDT <= $date_end ORDER BY CreatedDT DESC LIMIT 1440;";
			
		}else{
			$sql = "SELECT ServerId,php_pids,CreatedDT FROM ProdMonitor.MonitorLog WHERE serverid = $server_id ORDER BY CreatedDT DESC LIMIT 1440;";
		}
		$result = $this->db_conn->fetchAll($sql);
		foreach ($result as $row){
			$source = new DateTime($row['CreatedDT']);
			$gtime = 'Date('.$source->format('Y,n,j,G,i,s').')';
			$php_pids = round($row['php_pids'],2);
			$response['rows'][]['c'] = array(
				array('v' => $gtime ),
				array('v' => $php_pids ),
			);
		}
		return $response;
	}
	
	public function get_pull_log(){
		$response = array();
		$response['cols'] = array(
            array(
                'label' => 'Datetime',
                'type' => 'string',
            ),
            array(
                'label' => 'Server',
                'type' => 'string',
            ),
			array(
                'label' => 'Pass Rate',
                'type' => 'string',
            ),
			array(
                'label' => 'Pass',
                'type' => 'string',
            ),
			array(
                'label' => 'Fail',
                'type' => 'string',
            ),
        );
		$sql = "SELECT * FROM PullingLog ORDER BY Id DESC LIMIT 360;";
		$result = $this->db_conn->fetchAll($sql);
		foreach ($result as $row){
			$response['rows'][]['c'] = array(
				array('v' => $row['CreatedDT'] ),
				array('v' => $row['total_server'] ),
				array('v' => $row['pass_rate'] ),
				array('v' => $row['pass_server'] ),
				array('v' => $row['fail_server'] ),
			);
		}
		return $response;
	}
	

}