<?php

include 'vendor/autoload.php';

if ($argc < 2) {
	exit(0);
}

$pdo = new \PDO('mysql:host=localhost;dbname=music', 'root', '12345', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'	]);

$Mapper = new \Mp3\FileMapper($pdo);

$files = array_slice($argv, 1);

foreach ($files as $file) {
	$Mp3 = new \Mp3\FileInfo($file);
	$Mp3->readInfo();
	if (!$Mapper->save($Mp3)) {
		printf("ERR: %s\n", $Mp3->getFile()->getPathName());
	}
	else {
		printf("OK	: %s\n", $Mp3->getFile()->getFileName());
	}
}
exit(0);
