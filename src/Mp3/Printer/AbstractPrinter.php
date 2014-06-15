<?php

namespace Mp3\Printer;

abstract class AbstractPrinter
{
	public function run(\Mp3\Collection\UnitInterface $Unit)
	{
		$this->_printHeader();

		$this->printTree($Unit);

		$this->_printFooter();
	}

	protected function _printHeader()
	{
	}

	protected function _printFooter()
	{
	}

	public function printTree(\Mp3\Collection\UnitInterface $Unit, $level = 0)
	{
		if ($Unit instanceof \Mp3\Collection)
		{
			$this->_printBeforeCollection($Unit, $level);

			$children = $Unit->getChildren();

			ksort($children);

			foreach ($children as $child) {
				$this->printTree($child, $level + 1);
			}

			$this->_printAfterCollection($Unit, $level);
		}
		else
		{
			$this->_printFile($Unit, $level);
		}
	}

	abstract protected function _printBeforeCollection(\Mp3\Collection $Collection, $level);

	protected function _printAfterCollection(\Mp3\Collection $Collection, $level)
	{
		return;
	}

	abstract protected function _printFile(\Mp3\FileInfo $File, $level);

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

	public static function formatBitrate($val)
	{
		$result = [];

		foreach ($val as $type => $rates) {
			if ($type == 'CBR') {
				$type = '';
			}
			else {
				$type .= ' ';
			}
			if (!empty($rates)) {
				$rates = array_keys($rates);
				$min = min($rates) / 1000;
				$max = max($rates) / 1000;
				if ($min == $max) {
					$result[$type] = sprintf('%s%d kbps', $type, $min);
				}
				else {
					$result[$type] = sprintf('%s%d - %d Kbps', $type, $min, $max);
				}
			}
		}

		return implode(', ', $result);
	}
}

