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

function format_time($s) {
	$h = $m = 0;
	if ($s > 3600) {
		$h = floor($s / 3600);
		$s -= $h * 3600;
	}
	if ($s > 60) {
		$m = floor($s / 60);
		$s -= $m * 60;
	}
	if ($h) {
		return sprintf('%02d:%02d:%02d', $h, $m, $s);
	}
	else {
		return sprintf('%02d:%02d', $m, $s);
	}
}

function go($key, $Col, $j = 0) {
	echo str_repeat("\t", $j);
	if ($Col instanceof Mp3Collection) {
		$children = $Col->getChildren();
		printf("[[[ %s (%s) ]]]\n", $Col->getFile()->getBaseName(), format_time($Col->getTotalTime()), count($children));
		ksort($children, SORT_STRING);
		foreach ($children as $key => $child) {
			go($key, $child, $j + 1);
		}
	}
	else {
		printf("%s (%s)\n", $Col->getFile()->getBasename('.mp3'), format_time($Col->getTotalTime()));
	}
}


go($Col->getRoot(), $Col);
