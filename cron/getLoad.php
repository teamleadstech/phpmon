<?php

if (PHP_SAPI !== 'cli') {
    exit('hello world!');
}

require_once '../config.php';

$input = Tool::cmdInput();

if($input == SERVER_KEY){
	$report = new ServerReport(false);
	echo $report->getLoadData();
}