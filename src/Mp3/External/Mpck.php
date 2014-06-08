<?php

namespace Mp3\External;

class Mpck implements ProviderInterface
{
	const EXEC_STRING = '/usr/bin/mpck %s';

	private $_filePath;

	public function __construct($file_path)
	{
		$this->_filePath = $file_path;
	}

	private function _parseSampleRateLine($line)
	{
		$params = [];
		if (preg_match('@(\d+) Hz@', $line, $matches))
		{
			$params['sample_rate'] = (int) $matches[1];
		}
		return $params;
	}

	private function _parseTimeLine($line)
	{
		$params = [];
		if (preg_match('@(\d+):(\d+)\.(\d+)@', $line, $matches))
		{
			$time_sec = $matches[1] * 60 + $matches[2] + $matches[3] * 0.001;
			$params['time'] = (float) $time_sec;
		}

		return $params;
	}

	private function _parseBitrateLine($line)
	{
		$params = [];
		if (preg_match('@(\d+) bps(?: \((VBR|CBR|ABR)\))?@', $line, $matches))
		{
			$params['bitrate'] = (int) $matches[1];

			$type = !empty($matches[2]) ? $matches[2] : 'CBR';
			$params['bitrate_type'] = $type;
		}

		return $params;
	}

	public function execute()
	{
		$file_path = escapeshellarg($this->_filePath);
		$exec = sprintf(self::EXEC_STRING, $file_path);
		exec($exec, $output, $retval);

		if ($retval !== 0 && $retval !== 1)
		{
			return false;
		}

		if (count($output) <= 3)
		{
			return false;
		}
		$params = [];

		foreach ($output as $line)
		{
			$line = trim($line);
			if (!$line)
			{
				continue;
			}
			if (!preg_match('@^\s*(?P<param>.+?)\s{2,}+(?P<value>.+)@', $line, $matches))
			{
				continue;
			}
			$line = $matches['param'];
			$value = $matches['value'];

			switch ($line)
			{
				case 'samplerate':
					$params += $this->_parseSampleRateLine($value);
					break;
				case 'frames':
					$params['frames'] = (int) $value;
					break;
				case 'time':
					$params += $this->_parseTimeLine($value);
					break;
				default:
					break;
			}

			if (strpos($line, 'bitrate') !== false)
			{
				$params += $this->_parseBitrateLine($value);
			}
		}

		return $params;
	}
}
