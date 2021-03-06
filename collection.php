<?php

include 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=music', 'root', '12345', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);

if ($argc <= 2) {
	exit(1);
}
$path = $argv[1];
$slice_parts = $argv[2];

$Col = new \Mp3\Collection($path, $slice_parts);

$Mapper = new \Mp3\FileMapper($pdo);
$all = $Mapper->getAll();

foreach ($all as $File) {
	$Col->addFile($File);
}


$Printer = new \Mp3\Printer\Forum();
$Printer->run($Col);
