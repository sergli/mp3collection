<?php

class Mp3FileMapper
{
	private $_pdo;

	public function __construct(PDO $pdo) {
		$this->_pdo = $pdo;
	}

	public function saveFile(Mp3File $Mp3File) {
		$sql = "
		INSERT INTO music.files
		(
			file_path,
			time,
			frames,
			sample_rate,
			mpeg_version,
			layer,
			bitrate,
			bitrate_type
		)
		VALUES
		(
			:file_path,
			:time,
			:frames,
			:sample_rate,
			:mpeg_version,
			:layer,
			:bitrate,
			:bitrate_type
		)";

		$stmt = $this->_pdo->prepare($sql);

		$params = $Mp3File->toArray();
		$keys = array_keys($params);
		$keys = array_map(function($val) {
			return ':' . $val;
		}, $keys);
		$params = array_combine($keys, $params);

		$res = $stmt->execute($params);

		$Mp3File->setId($this->_pdo->lastInsertId());

		return $res;
	}

	public function saveTags(Mp3File $Mp3File) {
		if (!$Mp3File->getId()) {
			return false;
		}

		$sql = "
		INSERT INTO music.tags
		(
			file_id,
			artist,
			album,
			title,
			`year`,
			genre,
			track,
			tracks,
			other
		)
		VALUES
		(
			:file_id,
			:artist,
			:album,
			:title,
			:year,
			:genre,
			:track,
			:tracks,
			:other
		)";
		$stmt = $this->_pdo->prepare($sql);

		$params = $Mp3File->getTags()->toArray();
		$params['file_id'] = $Mp3File->getId();
		$keys = array_keys($params);
		$keys = array_map(function($val) {
			return ':' . $val;
		}, $keys);
		$params = array_combine($keys, $params);

		$res = $stmt->execute($params);

		return $res;
	}
}
