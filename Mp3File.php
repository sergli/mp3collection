<?php

class Mp3File
{
	const EXEC_MPCK = '/usr/bin/mpck %s';
	const EXEC_EYED3 = '/usr/bin/eyeD3 --no-color %s';

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
		'mpeg_version'	=> null,
		'layer'			=> null,
		'bitrate'		=> null,
		'bitrate_type'	=> null,
	];


	public static $bitrateTypes = ['CBR', 'VBR', 'ABR'];

	public function __construct($fileName) {
		$this->setFile($fileName);
		$this->_tags = new Mp3Tags($this);
	}

	public function setFile($fileName) {
		$this->_params = array_fill_keys(array_keys($this->_params), null);
		$this->_file = new SplFileInfo($fileName);
		$this->_params['file_path'] = $this->_file->getPathName();
	}

	public function getFile() {
		return $this->_file;
	}

	public function toArray() {
		return $this->_params;
	}


	private function _setTime($val) {
		//	time in seconds
		if (is_numeric($val)) {
			$this->_params['time'] = (float) $val;
			return true;
		}
		//	set time from mpck output
		if (preg_match('@(\d+):(\d+)\.(\d+)@', $val, $matches)) {
			$this->_params['time'] = $matches[1] * 60 + $matches[2] + $matches[3] * 0.001;
			return true;
		}

		return false;
	}

	private function _setSampleRate($val) {
		if (is_numeric($val)) {
			$this->_params['sample_rate'] = (int) $val;
			return true;
		}

		if (preg_match('@(\d+) Hz@', $val, $matches)) {
			$this->_params['sample_rate'] = (int) $matches[1];
			return true;
		}
		return false;
	}


	private function _setBitrate($val) {
		if (is_numeric($val)) {
			return $this->_params['bitrate'] = (int) $val;
		}

		if (preg_match('@(\d+) bps(?: \((VBR|CBR|ABR)\))?@', $val, $matches)) {
			$this->_params['bitrate'] = (int) $matches[1];

			$type = !empty($matches[2]) ? $matches[2] : 'CBR';
			if (in_array($type, self::$bitrateTypes)) {
				$this->_params['bitrate_type'] = $type;
			}

			return true;
		}

		return false;
	}

	public function getTags() {
		return $this->_tags;
	}

	public function __get($param) {
		$param = strtolower($param);
		if (isset($this->_params[$param])) {
			return $this->_params[$param];
		}

		return null;
	}

	public function __set($param, $value) {
		$param = strtolower($param);

		switch ($param) {
		case 'file_id':
			$this->_params['file_id'] = !is_null($param) ? (int) $value : null;
			break;
		case 'time':
			$this->_setTime($value);
			break;
		case 'frames':
			$this->_params['frames'] = (int) $value;
			break;
		case 'samplerate':
			$this->_setSampleRate($value);
			break;
		case 'version':
			$this->_params['mpeg_version'] = $value;
			break;
		case 'layer':
			$this->_params['layer'] = (int) $value;
			break;
		case 'bitrate_type':
			$this->_params['bitrate_type'] = (int) $value;
			break;
		}

		if (strpos($param, 'bitrate') !== false) {
			$this->_setBitrate($value);
		}
	}

	public function readInfo() {
		$exec = sprintf(self::EXEC_MPCK,
			escapeshellarg($this->_file->getPathName()));
		exec($exec, $output, $retval);

		if ($retval !== 0 && $retval !== 1) {
			return false;
		}

		if (count($output) <= 3) {
			return false;
		}

		foreach ($output as $line) {
			$line = trim($line);
			if (!$line) {
				continue;
			}
			if (!preg_match('@^(.+?)\s{2,}+(.+)@', $line, $matches)) {
				continue;
			}

			$this->__set($matches[1], $matches[2]);
		}
	}

	public function readTags() {
		$this->_tags->readTags();
	}
}
