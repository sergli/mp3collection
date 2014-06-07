<?php

class Mp3File
{
	const CBR = 0b00;
	const VBR = 0b01;
	const ABR = 0b10;

	const EXEC_MPCK = '/usr/bin/mpck %s';
	const EXEC_EYED3 = '/usr/bin/eyeD3 --no-color %s';

	private $_file;

	private $_tags = null;
	private $_time;
	private $_frames;
	private $_sampleRate;
	private $_mpegVersion;
	private $_layer;
	private $_bitrate;
	private $_bType;
	private $_id;

	public static $bitrateTypes = [
		self::CBR => 'CBR',
		self::VBR => 'VBR',
		self::ABR => 'ABR',
	];

	public function setId($id) {
		$this->_id = $id;
	}
	public function getId() {
		return $this->_id;
	}

	public function __construct($fileName) {
		$this->_file = new SplFileInfo($fileName);
		$this->_tags = new Mp3Tags($this->_file);
	}

	public function toArray() {
		return [
			'file_path'		=> $this->_file->getPathName(),
			'time'			=> $this->_time,
			'frames'		=> $this->_frames,
			'sample_rate'	=> $this->_sampleRate,
			'mpeg_version'	=> $this->_mpegVersion,
			'layer'			=> $this->_layer,
			'bitrate'		=> $this->_bitrate,
			'bitrate_type'	=> $this->getBitrateType(),
		];
	}

	public function getBitrateType() {
		return self::$bitrateTypes[$this->_bType];
	}

	public function getFile() {
		return $this->_file;
	}

	public function setTime($time) {
		if (!preg_match('@(\d+):(\d+)\.(\d+)@', $time, $matches)) {
			return false;
		}
		$this->_time = $matches[1] * 60 + $matches[2] + $matches[3] * 0.001;
		return $this;
	}

	public function setFrames($frames) {
		$this->_frames = (int) $frames;
	}

	public function setSampleRate($sampleRate) {
		if (!preg_match('@(\d+) Hz@', $sampleRate, $matches)) {
			return false;
		}
		$this->_sampleRate = (int) $matches[1];
		return $this;
	}

	public function setMpegVersion($version) {
		$this->_mpegVersion = $version;
		return $this;
	}

	public function setlayer($layer) {
		$this->_layer = (int) $layer;
	}


	public function setBitrate($bitrate) {
		if (!preg_match('@(\d+) bps(?: \((VBR|CBR|ABR)\))?@', $bitrate, $matches)) {
			return false;
		}

		$this->_bitrate = (int) $matches[1];

		$type = !empty($matches[2]) ? $matches[2] : '';

		switch ($type) {
		case 'VBR':
			$this->_bType = self::VBR;
			break;
		case 'ABR':
			$this->_bType = self::ABR;
			break;
		case 'CBR':
		default:
			$this->_bType = self::CBR;
			break;
		}

		return $this;
	}

	public function getTags() {
		return $this->_tags;
	}

	public function setTag($tag, $value) {
		$tag = strtolower($tag);
		switch ($tag) {
		case 'artist':
		case 'album':
		case 'genre':
		case 'title':
		case 'track':
		case 'year':
			$this->_tags[$tag] = $value;
			break;
		default:
			$this->_tags['other'][$tag] = $value;
		}
	}

	public function setProperty($property, $value) {
		switch ($property) {
		case 'time':
			$this->setTime($value);
			break;
		case 'frames':
			$this->setFrames($value);
			break;
		case 'samplerate':
			$this->setSampleRate($value);
			break;
		case 'version':
			$this->setMpegVersion($value);
			break;
		case 'layer':
			$this->setLayer($value);
			break;
		}

		if (strpos($property, 'bitrate') !== false) {
			$this->setBitrate($value);
		}

		return $this;
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

			$this->setProperty($matches[1], $matches[2]);
		}
	}

	public function readTags() {
		$this->_tags->readTags();
	}
}
