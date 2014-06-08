<?php

namespace Mp3\Printer;

class Cli extends \Mp3\Printer\AbstractPrinter
{
	protected function _visitAll($Obj, $level = 0)
	{
		echo str_repeat("\t", $level);

		if ($Obj instanceof \Mp3\Collection)
		{
			$children = $Obj->getChildren();

			printf("[[[ %s (time: %s, files: %d) ]]]\n",
				$Obj->getFile()->getBaseName(),
				self::formatTime($Obj->getTotalTime()),
				$Obj->getTotalFiles(),
				count($children)
			);

			ksort($children, SORT_STRING);

			foreach ($children as $child)
			{
				$this->_visitAll($child, $level + 1);
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
