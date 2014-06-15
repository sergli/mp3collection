<?php

namespace Mp3\Collection;

interface UnitInterface
{
	public function getTotalTime();

	public function getTotalFiles();

	public function getTotalBitrates();

	public function getTotalYears();

	public function getName();
}
