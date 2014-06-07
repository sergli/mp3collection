<?php

if ($argc < 2) {
	exit(0);
}

require_once __DIR__ . '/Mp3File.php';
require_once __DIR__ . '/Mp3Tags.php';
require_once __DIR__ . '/Mp3FileMapper.php';
ob_implicit_flush(true);

$pdo = new PDO('mysql:host=localhost;dbname=music', 'root', '12345', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'	]);

$DataMapper = new Mp3FileMapper($pdo);

$files = array_slice($argv, 1);

foreach ($files as $file) {
	$Mp3 = new Mp3File($file);
	$Mp3->readInfo();
	$Mp3->readTags();

	if (!$DataMapper->saveFile($Mp3)) {
		printf("ERR	(saveFile): %s\n", $Mp3->getFile()->getFileName());
	}
	else if (!$DataMapper->saveTags($Mp3)) {
		printf("ERR	(saveTags): %s\n", $Mp3->getFile()->getFileName());
	}
	else {
		printf("OK	: %s\n", $Mp3->getFile()->getFileName());
	}
}
exit(0);
