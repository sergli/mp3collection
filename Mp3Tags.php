<?php

class Mp3Tags
{
	const EXEC_EYED3 = '2>/dev/null /usr/bin/eyeD3 --no-color %s';

	private $_file;

	private $_tags = [
		'artist'	=> null,
		'album'		=> null,
		'title'		=> null,
		'year'		=> null,
		'genre'		=> null,
		'track'		=> null,
		'tracks'	=> null,
		'other'		=> null,
	];

	public function __construct(Mp3File $File) {
		$this->_file = $File;
	}

	public function getFile() {
		return $this->_file;
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
			$this->_tags['other'][$name] = $value;
			break;
		}

	}

	public function toArray() {

		$arr = [
			'file_id'	=> $this->_file->file_id,
		];

		$tags = $this->_tags;

		if (isset($tags['other'])) {
			$other = [];
			foreach ($tags['other'] as $key => $val) {
				$other[] = "$key: $val";
			}
			$other = implode('|', $other);

			$tags['other'] = $other;
		}
		$arr = $tags + $arr;

		return $arr;
	}

	public function readTags()
	{
		$exec = sprintf(self::EXEC_EYED3,
			escapeshellarg($this->_file->file_path));
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
