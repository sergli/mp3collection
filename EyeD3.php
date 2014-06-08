<?php

class EyeD3
{
	const EXEC_STRING = '/usr/bin/eyeD3 --no-color %s';

	private $_filePath;


	public function __construct($file_path) {
		$this->_filePath = $file_path;
	}

	public function execute() {
		$file_path = escapeshellarg($this->_filePath);
		$exec = sprintf(self::EXEC_STRING, $file_path);
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

		$params = [];
		foreach ($tags as $tag => $value) {
			switch ($tag) {
				case 'year':
					$params[$tag] = (int) $value;
					break;
				case 'track':
					$params += $this->_parseTrackLine($value);
					break;
				case 'genre':
					$params += $this->_parseGenreLine($value);
					break;
				case 'artist':
				case 'album':
				case 'title':
				default:
					$params[$tag] = $value;
					break;
			}
		}

		return $params;
	}

	private function _parseTrackLine($line) {
		$params = [];
		if (preg_match('@(?P<track>\d+)(?:/(?P<tracks>\d+))?@', $line, $matches)) {
			$params['track'] = (int) $matches['track'];
			if (isset($matches['tracks'])) {
				$params['tracks'] = (int) $matches['tracks'];
			}
		}
		return $params;
	}

	private function _parseGenreLine($line) {
		$params = [];
		if (preg_match('@(?P<genre>[^(]+)(?: \(id (?P<id>.*)\))@', $line, $matches)) {
			$params['genre'] = $matches['genre'];
		}
		return $params;
	}
}
