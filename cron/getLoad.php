<?php

if (PHP_SAPI !== 'cli') {
    exit('hello world!');
}

require_once '../config.php';

$input = Tool::cmdInput();

if($input == '9059025341'){
	$report = new ServerReport();
	echo $report->getLoadData();
}