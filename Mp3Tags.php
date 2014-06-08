<?php

class Mp3Tags
{
	private $_file;

	private $_tags = [
		'artist'	=> null,
		'album'		=> null,
		'title'		=> null,
		'year'		=> null,
		'genre'		=> null,
		'track'		=> null,
		'tracks'	=> null,
		'other'		=> [],
	];

	public function __construct(Mp3File $File) {
		$this->_file = $File;
	}

	public function getExternalProvider() {
		return new EyeD3($this->_file->file_path);
	}

	public function toArray() {
		return $this->_tags +
			['file_id' => $this->_file->file_id];
	}

	public function getFile() {
		return $this->_file;
	}

	public function readTags() {
		$params = $this->getExternalProvider()->execute();
		foreach ($params as $param => $value) {
			$this->{$param} = $value;
		}
	}

	public function __isset($name) {
		return array_key_exists($name, $this->_tags);
	}

	public function __get($name) {
		if (isset($this->_tags[$name])) {
			return $this->_tags[$name];
		}
		return null;
	}

	public function __set($name, $value) {
		$name = strtolower($name);
		if (array_key_exists($name, $this->_tags)) {
			$this->_tags[$name] = $value;
		}
		else {
			$this->_tags['other'][$name] = $value;
		}
	}
}
