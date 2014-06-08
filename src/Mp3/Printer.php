<?php

namespace Mp3;

class Printer
{
	public static function printCollection(\Mp3\Collection $Collection)
	{
		self::visitAll($Collection);
	}

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

	public static function visitAll($Obj, $j = 0)
	{
		echo str_repeat("\t", $j);

		if ($Obj instanceof \Mp3\Collection)
		{
			$children = $Obj->getChildren();

			printf("[[[ %s (%s) ]]]\n",
				$Obj->getFile()->getBaseName(),
				self::formatTime($Obj->getTotalTime()),
				count($children)
			);

			ksort($children, SORT_STRING);

			foreach ($children as $child)
			{
				self::visitAll($child, $j + 1);
			}
		}
		else
		{
			printf("%s (%s)\n",
				$Obj->getFile()->getBasename('.mp3'),
				self::formatTime($Obj->getTotalTime())
			);
		}
	}
}

