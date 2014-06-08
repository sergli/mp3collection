<?php

namespace Mp3\Printer;

abstract class AbstractPrinter
{
	protected $_Collection;

	public function __construct(\Mp3\Collection $Collection)
	{
		$this->_Collection = $Collection;
	}

	public function printCollection()
	{
		$this->_visitAll($this->_Collection);
	}

	abstract protected function _visitAll($Obj, $level = 0);

	public static function formatTime($s)
	{
		$h = $m = 0;
		if ($s > 3600)
		{
			$h = floor($s / 3600);
			$s -= $h * 3600;
		}
		if ($s > 60)
		{
			$m = floor($s / 60);
			$s -= $m * 60;
		}
		if ($h)
		{
			return sprintf('%02d:%02d:%02d', $h, $m, $s);
		}
		else
		{
			return sprintf('%02d:%02d', $m, $s);
		}
	}

}

