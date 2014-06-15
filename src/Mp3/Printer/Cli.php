<?php

namespace Mp3\Printer;

class Cli extends \Mp3\Printer\AbstractPrinter
{
	protected function _printCollection(\Mp3\Collection $Collection, $level)
	{
		$fmt = str_repeat("\t", $level);

		$params = [
			$Collection->getName(),
			self::formatTime($Collection->getTotalTime()),
			$Collection->getTotalFiles(),
		];

		$fmt .= '[[[ %s (time: %s, files: %d';
		if ($Collection->isAlbum())
		{
			$fmt .= ', %s';
			$params[] = self::formatBitrate($Collection->getTotalBitrates());
		}
		$fmt .= ') ]]]' . "\n";

		vprintf($fmt, $params);
	}

	protected function _printFile(\Mp3\FileInfo $File, $level)
	{
		$fmt = str_repeat("\t", $level);
		$fmt .= '%s (%s)' . "\n";
		$params = [
			$File->getName(),
			self::formatTime($File->getTotalTime()),
		];
		vprintf($fmt, $params);
	}
}
