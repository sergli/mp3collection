<?php

namespace Mp3\Printer;

class Forum extends \Mp3\Printer\AbstractPrinter
{
	private function printf($str, $level = 0, array $args = [])
	{
		$indent = str_repeat(' ', $level);
		$str = $indent . rtrim($str) . "\n";
		vprintf($str, $args);
	}

	protected function _printHeader()
	{
		$this->printf('[clear]');
	}

	protected function _printBeforeCollection(\Mp3\Collection $Collection, $level)
	{
		if ($level === 0) {
			return;
		}

		$name = $Collection->getName();
		$time = self::formatTime($Collection->getTotalTime());
		if ($Collection->isAlbum())
		{
			$bitrate = self::formatBitrate($Collection->getTotalBitrates());
			$this->printf('[spoiler="%s | %s"]', $level, [$name, $bitrate]);
//			$this->printf('[img=right]http://%s[/img]', $level, [$name]);
			$this->printf('[b]Продолжительность[/b]: %s', $level + 1, [$time]);
			$this->printf('[b]Треклист[/b]:', $level + 1);
		}
		else
		{
			$this->printf('[spoiler="%s | %s"]', $level, [$name, $time]);
		}
	}
	protected function _printAfterCollection(\Mp3\Collection $Collection, $level)
	{
		$this->printf('[/spoiler]', $level);
	}


	protected function _printFile(\Mp3\FileInfo $File, $level)
	{
		if ($level === 0) {
			return;
		}

		$this->printf('%s (%s)', $level, [
			$File->getFile()->getBaseName('.mp3'),
			self::formatTime($File->getTotalTime())
		]);
	}
}
