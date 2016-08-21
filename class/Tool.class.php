<?php
/**
 * Tool - a static class to make life easier
 * User: Samuel Zhang
 * Date: 2016-08-12
 */
 
class Tool
{

	public static function cmdInput(){
		$handle = fopen ("php://stdin","r");
		$line = fgets($handle);
		return trim($line);
	}
	
	public static function cmd($cmd, $arr_output = true, $debug = false ){
		if($debug){
			echo "\033[41m".$cmd."\033[0m\n";
		}
		if($arr_output){
			exec($cmd,$result);
		}else{
			$result = shell_exec($cmd);
		}
		return $result;
	}
	
	public static function humanSize($filesize, $input_unit = false, $precision = 2){
		$units = array('', 'K', 'M', 'G', 'T');
		
		if($input_unit){
			foreach ($units as $key => $val){
				if($val == $input_unit){
					break;
				}
				unset($units[$key]);
			}
		}
		
		foreach ($units as $key => $val){
			if ($filesize > 1024){
				$filesize /= 1024;
			}else{
				break;
			}
		}
		return round($filesize, $precision).' '.$units[$key].'B';
	}
	
	public static function humanTime($seconds){
		$result = '';
		$time_units = array(
			'y' => 'yr',
			'm' => 'mon',
			'd' => 'day',
			'h' => 'hr',
			'i' => 'min',
		);
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		$interval = $dtF->diff($dtT);
		foreach ($time_units as $key => $val){
			if($interval->$key > 0){
				$result .= " ".$interval->$key." $val,";
			}
		}
		$result = trim(rtrim($result,','));
		return $result;
    }

}