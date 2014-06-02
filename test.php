<?php

$file = './2.mp3';

require_once __DIR__ . '/Mp3File.php';
require_once __DIR__ . '/Mp3FileMapper.php';

$Mp3 = new Mp3File($file);
$Mp3->execMpck();

var_dump($Mp3);

/*
$exec = '/usr/bin/eyeD3 --rfc %s';
$exec = sprintf($exec,
	escapeshellarg($Mp3->getFile()->getFileName()));

var_dump(shell_exec($exec));
 */

$pdo = new PDO('mysql:host=localhost;dbname=music', 'root', '12345', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'	]);

$DataMapper = new Mp3FileMapper($pdo);

var_dump($DataMapper->saveMp3File($Mp3));
