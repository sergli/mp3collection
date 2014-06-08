<?php

include 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=music', 'root', '12345', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'	]);

$Col = new \Mp3\Collection('/ipv-OK/', 2);

$Mapper = new \Mp3\FileMapper($pdo);
$all = $Mapper->getAll();

foreach ($all as $File) {
	$Col->addFile($File);
}


$Printer = new \Mp3\Printer\Cli($Col);
$Printer->printCollection();
