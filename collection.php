<?php

include_once 'init.php';

ini_set('xdebug.var_display_max_children', 1024);
ini_set('xdebug.var_display_max_data', 4096);
ini_set('xdebug.var_display_max_depth', 100);

$pdo = new PDO('mysql:host=localhost;dbname=music', 'root', '12345', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'	]);

$Col = new Mp3Collection('/ipv-OK/', 2);

$Mapper = new Mp3FileMapper($pdo);
$all = $Mapper->getAll();

foreach ($all as $File) {
	$Col->addFile($File);
}

Printer::printCollection($Col);
