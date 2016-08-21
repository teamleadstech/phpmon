<?php

if (PHP_SAPI !== 'cli') {
    exit('hello world!');
}

require_once '../config.php';

echo date('Y-m-d H:i:s')." Job Start... \n";

if(isset($argv[1]) && $argv[1] == 'example'){
    echo "Example Job \n";
}

if(isset($argv[1]) && $argv[1] == 'test'){
    //echo Tool::humanSize(528315064,'K');
    //echo Tool::humanTime(3600);
	//var_dump(Tool::cmd('df -h',false));
	print_r(ServerInfo::getCpuInfo());
	print_r(ServerInfo::getDiskInfo());
	print_r(ServerInfo::getOsInfo());
	print_r(ServerInfo::getMemInfo());
	print_r(ServerInfo::getLoadInfo());
}

if(isset($argv[1]) && $argv[1] == 'testdb'){
    echo "Test Database Driver \n";
    $db = new PdoConnection();
	var_dump($db->isConnected());
	$sql = "SELECT NOW();";
	print_r($db->fetchAll($sql));
}


if(isset($argv[1]) && $argv[1] == 'syncspecs'){
    echo "Sync Server Specs... \n";
	$report = new ServerReport();
	var_dump($report->syncServerSpecs());
}

if(isset($argv[1]) && $argv[1] == 'reportload'){
    echo "Report Server Load... \n";
	$report = new ServerReport();
	var_dump($report->reportLoad());
}


echo date('Y-m-d H:i:s')." Job End... \n";

