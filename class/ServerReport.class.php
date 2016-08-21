<?php

/**
 * ServerReport - report to server monitor log database
 * User: Samuel Zhang
 * Date: 2016-08-12
 */
 
 
class ServerReport
{
	protected $db_conn;
	
	public function __construct($db = true){
		if($db){
			$this->db_conn = new PdoConnection();
		}
	}
	
	public function syncServerSpecs(){
		$cpu_info = ServerInfo::getCpuInfo();
		$disk_info = ServerInfo::getDiskInfo();
		$os_info = ServerInfo::getOsInfo();
		$mem_info = ServerInfo::getMemInfo();
		
		$os = $os_info['os'];
		$kernel = $os_info['kernel'];
		$boot = $os_info['reboot'];
		$model = $cpu_info['model'];
		$core = $cpu_info['cores'];
		$speed = $cpu_info['frequency'];
		$cache = $cpu_info['cache'];
		$disk = $disk_info['total'];
		$mem = $mem_info['total'];
		$server = SERVER_ID;
		
		$sql = "UPDATE `MonitorServer` SET `ServerOS`='$os', `ServerKernel`='$kernel', `LastBoot`='$boot', `CPUModel`='$model', `CPUCore`='$core', `CPUSpeed`='$speed', `CPUCache`='$cache', `DiskSize`='$disk', `MemSize`='$mem' WHERE `ServerId`='$server';";
		$result = $this->db_conn->query($sql);
		if($result){
			return $result->rowCount()." rows affected...\n";
		}
		return false;
	}
	
	public function reportLoad(){
		$disk_info = ServerInfo::getDiskInfo();
		$mem_info = ServerInfo::getMemInfo();
		$load_info = ServerInfo::getLoadInfo();
		
		$server = SERVER_ID;
		$users = $load_info['users'];
		$who_info = $load_info['who'];
		$mem_use_kb = $mem_info['used'];
		$mem_free_kb = $mem_info['free'];
		$mem_total_kb = $mem_info['total'];
		$mem_use_pert = $mem_info['use_pert'];
		$disk_use_kb = $disk_info['used'];
		$disk_free_kb = $disk_info['free'];
		$disk_total_kb = $disk_info['total'];
		$disk_use_pert = $disk_info['use_pert'];
		$load_1min = $load_info['load_1min'];
		$load_1min_pert = $load_info['load_1min_pert'];
		$load_5min = $load_info['load_5min'];
		$load_5min_pert = $load_info['load_5min_pert'];
		$load_15min = $load_info['load_15min'];
		$load_15min_pert = $load_info['load_15min_pert'];
		$total_pids = $load_info['total_pids'];
		$php_pids = $load_info['php_pids'];
		$disk_tps = $load_info['disk_tps'];
		$disk_rtps = $load_info['disk_rtps'];
		$disk_wtps = $load_info['disk_wtps'];
		$disk_rkbs = $load_info['disk_rkbs'];
		$disk_wkbs = $load_info['disk_wkbs'];
		
		$sql = "INSERT INTO `MonitorLog` (`ServerId`, `users`, `who_info`, `mem_use_kb`, `mem_free_kb`, `mem_total_kb`, `mem_use_pert`, `disk_use_kb`, `disk_free_kb`, `disk_total_kb`, `disk_use_pert`, `load_1min`, `load_1min_pert`, `load_5min`, `load_5min_pert`, `load_15min`, `load_15min_pert`, `total_pids`, `php_pids`, `disk_tps`, `disk_rtps`, `disk_wtps`, `disk_rkbs`, `disk_wkbs`) VALUES ('$server', '$users', '$who_info', '$mem_use_kb', '$mem_free_kb', '$mem_total_kb', '$mem_use_pert', '$disk_use_kb', '$disk_free_kb', '$disk_total_kb', '$disk_use_pert', '$load_1min', '$load_1min_pert', '$load_5min', '$load_5min_pert', '$load_15min', '$load_15min_pert', '$total_pids', '$php_pids', '$disk_tps', '$disk_rtps', '$disk_wtps', '$disk_rkbs', '$disk_wkbs');";
		$result = $this->db_conn->query($sql);
		if($result){
			return $result->rowCount()." rows affected...\n";
		}
		return false;
		
	}
	
	public function genBash(){
		$str = '';
		$file = CRON_DIR.'get_load';
		$str .= "#!/bin/bash\n";
		$str .= "cd ".CRON_DIR." && php getLoad.php";
		if(!file_exists($file)){
			file_put_contents($file, $str);
		}
		chmod($file, 0755);
		return $file;
	}
	
	public function getLoadData(){
		$response = array();
		
		$response['cpu_info'] = ServerInfo::getCpuInfo();
		$response['os_info'] = ServerInfo::getOsInfo();
		$response['disk_info'] = ServerInfo::getDiskInfo();
		$response['mem_info'] = ServerInfo::getMemInfo();
		$response['load_info'] = ServerInfo::getLoadInfo();
		
		return json_encode($response);
	}
	
	public function collectData(){
		$sql = "SELECT ServerId,ServerExtIP,ServerPort,ServerKey FROM MonitorServer WHERE MonitorYN = 1;";
		$result = $this->db_conn->fetchAll($sql);
		if(!$result){
			return false;
		}
		$counter = count($result);
		$i = 1;
		$pass = array();
		$fail = array();
		foreach($result as $row){
			echo "[$i/$counter]Retrieving data from ".$row['ServerId']."...\n";
			$data = $this->getNodeData($row['ServerExtIP'],$row['ServerPort'],$row['ServerKey']);
			if($data){
				$data['ServerId'] = $row['ServerId'];
				$this->addMonitorLog($data);
				$pass[] = $row['ServerId'];
			}else{
				$fail[] = $row['ServerId'];
			}
			$i++;
		}
		return array(
			'total' => $counter,
			'rate' => round(count($pass)/$counter*100,2)."%",
			'pass' => $pass,
			'fail' => $fail,
			
		);
	}
	
	public function addMonitorLog($data){
		$server = $data['ServerId'];
		$users = $data['load_info']['users'];
		$who_info = $data['load_info']['who'];
		$mem_use_kb = $data['mem_info']['used'];
		$mem_free_kb = $data['mem_info']['free'];
		$mem_total_kb = $data['mem_info']['total'];
		$mem_use_pert = $data['mem_info']['use_pert'];
		$disk_use_kb = $data['disk_info']['used'];
		$disk_free_kb = $data['disk_info']['free'];
		$disk_total_kb = $data['disk_info']['total'];
		$disk_use_pert = $data['disk_info']['use_pert'];
		$load_1min = $data['load_info']['load_1min'];
		$load_1min_pert = $data['load_info']['load_1min_pert'];
		$load_5min = $data['load_info']['load_5min'];
		$load_5min_pert = $data['load_info']['load_5min_pert'];
		$load_15min = $data['load_info']['load_15min'];
		$load_15min_pert = $data['load_info']['load_15min_pert'];
		$total_pids = $data['load_info']['total_pids'];
		$php_pids = $data['load_info']['php_pids'];
		$disk_tps = $data['load_info']['disk_tps'];
		$disk_rtps = $data['load_info']['disk_rtps'];
		$disk_wtps = $data['load_info']['disk_wtps'];
		$disk_rkbs = $data['load_info']['disk_rkbs'];
		$disk_wkbs = $data['load_info']['disk_wkbs'];
		
		$sql = "INSERT INTO `MonitorLog` (`ServerId`, `users`, `who_info`, `mem_use_kb`, `mem_free_kb`, `mem_total_kb`, `mem_use_pert`, `disk_use_kb`, `disk_free_kb`, `disk_total_kb`, `disk_use_pert`, `load_1min`, `load_1min_pert`, `load_5min`, `load_5min_pert`, `load_15min`, `load_15min_pert`, `total_pids`, `php_pids`, `disk_tps`, `disk_rtps`, `disk_wtps`, `disk_rkbs`, `disk_wkbs`) VALUES ('$server', '$users', '$who_info', '$mem_use_kb', '$mem_free_kb', '$mem_total_kb', '$mem_use_pert', '$disk_use_kb', '$disk_free_kb', '$disk_total_kb', '$disk_use_pert', '$load_1min', '$load_1min_pert', '$load_5min', '$load_5min_pert', '$load_15min', '$load_15min_pert', '$total_pids', '$php_pids', '$disk_tps', '$disk_rtps', '$disk_wtps', '$disk_rkbs', '$disk_wkbs');";
		$result = $this->db_conn->query($sql);
		if($result){
			return $result->rowCount()." rows affected...\n";
		}
		return false;
	}
	
	public function getNodeData($server_ip,$server_port,$server_key){
		
		$fp = fsockopen($server_ip, $server_port, $errno, $errstr, 30);
		$response = '';
		if (!$fp) {
			echo "$errstr ($errno)\n";
			return false;
		} else {
			$out = $server_key."\n";
			fwrite($fp, $out);
			while (!feof($fp)) {
				$response .= fgets($fp, 1024);
			}
			fclose($fp);
		}
		$response = trim($response);
		//echo '{JSON_START}'.$response.'{JSON_END}';
		return json_decode($response,true);
	}

}