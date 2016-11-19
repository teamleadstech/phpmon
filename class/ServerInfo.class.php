<?php

/**
 * ServerInfo - get server info
 * User: Samuel Zhang
 * Date: 2016-08-12
 */
 
class ServerInfo
{
	public static function getCpuInfo(){
		$cpu_info = array(
			'model' => 'NA',
			'cores' => 'NA',
			'frequency' => 'NA',
			'cache' => 'NA',
			'bogomips' => 'NA',
			'idle' => '100',
			'user' => '0',
			'iowait' => '0',
			'sys' => '0',
		);
		$cpu_info['cores'] = trim(Tool::cmd('/bin/grep -c ^processor /proc/cpuinfo',false));
		$cpu_info['idle'] = trim(Tool::cmd('ps aux|awk \'NR > 0 { s +=$3 }; END {print s}\'',false));
		$cpu_info['user'] = trim(Tool::cmd('mpstat | grep all | awk \'{print $4}\'',false));
		$cpu_info['sys'] = trim(Tool::cmd('mpstat | grep all | awk \'{print $6}\'',false));
		$cpu_info['iowait'] = trim(Tool::cmd('mpstat | grep all | awk \'{print $7}\'',false));
		
		$cat_cpuinfo = Tool::cmd('cat /proc/cpuinfo',true);
		$break_flag = false;
		foreach ($cat_cpuinfo as $row){
			$parts = explode(':',$row);
			
			if(!$parts[0] || !$parts[1]){
				continue;
			}
			$key = trim($parts[0]);
			$val = trim($parts[1]);
			
			if(!$break_flag){
				$break_flag = $key;
			}else if($break_flag == $key){
				break;
			}
			
			switch(strtolower($key)){
				case 'model name':
					$cpu_info['model'] = $val;
					break;
				case 'cpu mhz':
					$cpu_info['frequency'] = $val.' MHz';
					break;
				case 'cache size':
					$cpu_info['cache'] = $val;
					break;
				case 'bogomips':
					$cpu_info['bogomips'] = $val;
					break;	
			}
			
			//echo "[$key][$val]\n";
		}
		
		return $cpu_info;
	}
	
	public static function getDiskInfo(){
		$disk_info = array(
			'total' => '0',
			'used' => '0',
			'free' => '0',
			'use_pert' => '0',
		);
		
		$cat_df = Tool::cmd('/bin/df -T -B 1024',true);
		unset($cat_df[0]);

		foreach ($cat_df as $row){
			$parts = preg_split('/\s+/',$row);
			if($parts[1] == 'tmpfs'){
				continue;
			}
			$disk_info['total'] += $parts[2];
			$disk_info['used'] += $parts[3];
			$disk_info['free'] += $parts[4];
		}
		$disk_info['use_pert'] = round($disk_info['used']/$disk_info['total']*100,2);
		return $disk_info;
	}
	
	public static function getMemInfo(){
		$mem_info = array(
			'total' => '0',
			'used' => '0',
			'free' => '0',
			'use_pert' => '0',
		);
		
		$free = Tool::cmd('grep MemFree /proc/meminfo | awk \'{print $2}\'',false);
		$buffers = Tool::cmd('grep Buffers /proc/meminfo | awk \'{print $2}\'',false);
		$cached = Tool::cmd('grep Cached /proc/meminfo | awk \'{print $2}\'',false);
		
		$total = (int)Tool::cmd('grep MemTotal /proc/meminfo | awk \'{print $2}\'',false);
		$real_free = (int)$free + (int)$buffers + (int)$cached;;
		$real_used = $total - $real_free;
		
		
		$mem_info['total'] = trim($total);
		$mem_info['used'] = trim($real_used);
		$mem_info['free'] = trim($real_free);
		$mem_info['use_pert'] = round($real_used/$total*100,2);
		
		return $mem_info;
	}
	
	public static function getOsInfo(){
		$os_info = array(
			'os' => 'NA',
			'kernel' => 'NA',
			'reboot' => 'NA',
			'lastlog' => 'NA',
		);
		
		$os_info['os'] = trim(Tool::cmd('cat /etc/system-release',false));
		$os_info['kernel'] = trim(Tool::cmd('/bin/uname -r',false));
		$os_info['lastlog'] = trim(Tool::cmd('last -n 20',false));
		
		$cat_uptime = trim(Tool::cmd('cat /proc/uptime',false));
		$parts = explode(' ', $cat_uptime);
		$os_info['reboot'] = date('Y-m-d H:i:s', time() - intval($parts[0]));
		
		return $os_info;
		
	}
	
	public static function getLoadInfo(){
		$load_info = array(
			'users' => 0,
			'who' => 'NA',
			'load_1min' => 0,
			'load_1min_pert' => 0,
			'load_5min' => 0,
			'load_5min_pert' => 0,
			'load_15min' => 0,
			'load_15min_pert' => 0,
			'total_pids' => 0,
			'php_pids' => 0,
			'disk_tps' => 0,
			'disk_rtps' => 0,
			'disk_wtps' => 0,
			'disk_rkbs' => 0,
			'disk_wkbs' => 0,
		);
		
		$cat_users = Tool::cmd('who',true);
		if(count($cat_users) > 0){
			$load_info['users'] = count($cat_users);
			$load_info['who'] = implode("\n",$cat_users);
		}
		
		$cat_ps = Tool::cmd('ps aux',true);
		$load_info['total_pids'] = count($cat_ps) - 3;#exclude title + self + ps aux
		
		$cat_ps_php = Tool::cmd('ps aux | grep php',true);
		$load_info['php_pids'] = count($cat_ps_php) - 3;#exclude self + ps aux + grep php
		
		$cpu_cores = trim(Tool::cmd('/bin/grep -c ^processor /proc/cpuinfo',false));
		
		$cat_load = Tool::cmd('cat /proc/loadavg | awk \'{print $1","$2","$3}\'',false);
		$load_parts = explode(',', $cat_load);
		
		$load_info['load_1min'] = trim($load_parts[0]);
		$load_info['load_5min'] = trim($load_parts[1]);
		$load_info['load_15min'] = trim($load_parts[2]);
		
		$load_info['load_1min_pert'] = round($load_info['load_1min']/$cpu_cores*100,2);
		$load_info['load_5min_pert'] = round($load_info['load_5min']/$cpu_cores*100,2);
		$load_info['load_15min_pert'] = round($load_info['load_15min']/$cpu_cores*100,2);
		
		$sar_info = Tool::cmd('sar -b | tail -2',false);
		$sar_parts = preg_split('/\s+/',$sar_info);
		$sector_size = Tool::cmd('lsblk -o NAME,PHY-SeC',false);
		
		$load_info['disk_tps'] = trim($sar_parts[2]);
		$load_info['disk_rtps'] = trim($sar_parts[3]);
		$load_info['disk_wtps'] = trim($sar_parts[4]);
		if(strpos($sector_size, '512') !== false){
			$load_info['disk_rkbs'] = round(trim($sar_parts[5])/2,2);
			$load_info['disk_wkbs'] = round(trim($sar_parts[6])/2,2);
		}else if (strpos($sector_size, '4096') !== false){
			$load_info['disk_rkbs'] = round(trim($sar_parts[5])*4,2);
			$load_info['disk_wkbs'] = round(trim($sar_parts[6])*4,2);
		}	
		return $load_info;
	}
}