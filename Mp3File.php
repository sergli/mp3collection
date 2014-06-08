<?php

class Mp3File
{
	/**
	 * @var SplFileInfo
	 */
	private $_file;
	/**
	 * @var Mp3Tags
	 */
	private $_tags;

	private $_params = [
		'file_id'		=> null,
		'file_path'		=> null,
		'time'			=> null,
		'frames'		=> null,
		'sample_rate'	=> null,
		'bitrate'		=> null,
		'bitrate_type'	=> null,
	];


	public static $bitrateTypes = ['CBR', 'VBR', 'ABR'];

	protected function getExternalProvider() {
		return new Mpck($this->_file->getPathName());
	}

	public function getTotalTime() {
		return $this->_params['time'];
	}

	public function __construct($fileName) {
		$this->setFile($fileName);
		$this->_tags = new Mp3Tags($this);
		$this->_infoProvider = new Mpck($fileName);
	}

	public function setFile($fileName) {
		$this->_params = array_fill_keys(
			array_keys($this->_params), null);
		$this->_file = new SplFileInfo($fileName);
		$this->_params['file_path'] = $this->_file->getPathName();
	}

	public function getFile() {
		return $this->_file;
	}

	public function toArray() {
		return $this->_params;
	}

	public function getTags() {
		return $this->_tags;
	}

	public function readInfo() {
		$params = $this->getExternalProvider()->execute();
		if (false === $params) {
			throw new Exception('Not an MP3 File');
		}
		foreach ($params as $param => $value) {
			$this->{$param} = $value;
		}
		$this->_tags->readTags();
	}

	public function __isset($name) {
		return isset($this->_params[$name]);
	}

	public function __get($name) {
		$name = strtolower($name);
		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		}
		return null;
	}

	public function __set($name, $value) {
		$name = strtolower($name);

		if (array_key_exists($name, $this->_params)) {
			$this->_params[$name] = $value;
		}
		else if (isset($this->_tags->{$name})) {
			$this->_tags->{$name} = $value;
		}
	}
}
