<?php

namespace Mp3;

class Collection implements \Mp3\Collection\UnitInterface
{
	private $_root;
	private $_file;
	private $_cutNum = 0;

	private $_children = [];

	private $_totalTime = 0;
	private $_totalFiles = 0;

	public function getChildren()
	{
		return $this->_children;
	}

	public function getTotalTime()
	{
		if ($this->_totalTime == 0)
		{
			$time = 0.0;
			foreach ($this->getChildren() as $child)
			{
				$time += $child->getTotalTime();
			}
			$this->_totalTime = $time;
		}

		return $this->_totalTime;
	}

	public function getTotalFiles()
	{
		if ($this->_totalFiles == 0)
		{
			$count = 0;
			foreach ($this->getChildren() as $child)
			{
				$count += $child->getTotalFiles();
			}
			$this->_totalFiles = $count;
		}

		return $this->_totalFiles;
	}

	public function getFile()
	{
		return $this->_file;
	}

	public function __construct($root, $cutNum = 0)
	{
		$this->_root = $root;
		$this->_file = new \SplFileInfo($root);
		if ($cutNum > 0)
		{
			$this->_cutNum = (int) $cutNum;
		}
	}

	public function getRoot()
	{
		return $this->_root;
	}

	public function hasFile($file)
	{
		$chunk = substr($file, 0, strlen($this->_root));
		if ($chunk !== $this->_root)
		{
			return false;
		}
		//	empty file name ?
		if (strlen($file) === strlen($this->_root))
		{
			return false;
		}

		return true;
	}

	private function getNextRoot($file)
	{
		$pos = strpos($file, '/', strlen($this->_root));
		if (false === $pos)
		{
			return false;
		}
		return substr($file, 0, $pos + 1);
	}

	private function _getFilePath(\Mp3\FileInfo $File)
	{
		$file_path = $File->file_path;
		if ($this->_cutNum > 0)
		{
			$file_path = preg_replace('@^(/[^/]+){' . $this->_cutNum . '}@', '', $file_path);
		}
		return $file_path;
	}

	public function addFile(\Mp3\FileInfo $File)
	{
		$file_path = $this->_getFilePath($File);

		if (!$this->hasFile($file_path))
		{
			return false;
		}
		$nextRoot = $this->getNextRoot($file_path);
		if (false === $nextRoot)
		{
			return $this->_children[$file_path] = $File;
		}

		if (!isset($this->_children[$nextRoot]))
		{
			$this->_children[$nextRoot] = new self($nextRoot, $this->_cutNum);
		}
		return $this->_children[$nextRoot]->addFile($File);
	}
}
