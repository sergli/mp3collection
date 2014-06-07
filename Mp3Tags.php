<?php

class Mp3Tags
{
	const EXEC_EYED3 = '/usr/bin/eyeD3 --no-color %s';

	private $_file;
	private $_tags = [
		'artist'	=> null,
		'album'		=> null,
		'title'		=> null,
		'year'		=> null,
		'genre'		=> null,
		'track'		=> null,
		'tracks'	=> null,
	];

	private $_otherTags = [];

	public function __construct(SplFileInfo $file) {
		$this->_file = $file;
	}

	public function __set($name, $value) {
		$name = strtolower($name);
		switch ($name) {
		case 'artist':
		case 'album':
		case 'title':
		case 'year':
			$this->_tags[$name] = $value;
			break;
		case 'genre':
			preg_match('@(?P<genre>[^(]+)(?: \(id (?P<id>.*)\))@', $value, $matches);
			$this->_tags[$name] = $matches['genre'];
			break;
		case 'track':
			preg_match('@(?P<track>\d+)(?:/(?P<tracks>\d+))?@', $value, $matches);
			$this->_tags['track'] = (int) $matches['track'];
			if (isset($matches['tracks'])) {
				$this->_tags['tracks'] = (int) $matches['tracks'];
			}
			break;
		default:
			$this->_otherTags[$tag] = $value;
			break;
		}

	}

	public function toArray() {
		$res = $this->_tags;

		if (empty($other)) {
			$res['other'] = null;
			return $res;
		}

		$other = [];
		foreach ($this->_otherTags as $key => $val) {
			$other[] = "$key: $val";
		}
		$other = implode('|', $other);

		$res['other'] = $other;

		return $res;
	}

	public function readTags()
	{
		$exec = sprintf(self::EXEC_EYED3,
			escapeshellarg($this->_file->getPathName()));
		exec($exec, $output, $retval);

		if ($retval !== 0) {
			return false;
		}

		$lines = [];
		$start = null;
		$end = null;
		foreach ($output as $j => $line) {
			$line = trim($line);

			if (isset($start)) {
				if (isset($end)) {
					break;
				}
				else if ($line == '') {
					$end = $j;
				}
				else {
					$lines[] = $line;
				}
			}
			else if (preg_match('@^ID3@', $line)) {
				$start = $j;
			}
			else {
				continue;
			}
		}

		$tags = [];
		foreach ($lines as $line) {
			$count = preg_match_all('@\t*(?P<tag>[^:]+): (?P<value>[^\t]+)@', $line, $matches, PREG_SET_ORDER);
			if ($count > 0) {
				foreach ($matches as $match) {
					$tags[$match['tag']] = $match['value'];
				}
			}
		}

		foreach ($tags as $tag => $value) {
			$this->__set($tag, $value);
		}
	}
}
