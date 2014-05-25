<?php

$file = './2.mp3';

$Mp3 = new Mp3File($file);
$Mp3->execMpck();

var_dump($Mp3);

class Mp3File
{
	const CBR = 0b00;
	const VBR = 0b01;
	const ABR = 0b10;

	const EXEC_MPCK = '/usr/bin/mpck %s';

	private $_file;

	private $_tags = [];
	private $_time;
	private $_frames;
	private $_sampleRate;
	private $_mpegVersion;
	private $_layer;
	private $_bitrate;
	private $_bType;

	public function __construct($fileName) {
		$this->_file = new SplFileInfo($fileName);
	}

	public function setTag($tagName, $value) {
		$this->_tags[$tagName] = $value;
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

	public function execMpck()
	{
		$exec = sprintf(self::EXEC_MPCK, escapeshellarg($this->_file->getFileName()));
		exec($exec, $output, $retval);

		if ($retval !== 0) {
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
}
