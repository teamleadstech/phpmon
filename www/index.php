<?php

require_once '../config.php';

$routing = new Routing();
$content = $routing->parseRoute($_SERVER['REQUEST_URI']);

echo $content;