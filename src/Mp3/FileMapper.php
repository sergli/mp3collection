<?php

namespace Mp3;

class FileMapper
{
	/**
	 * @var \PDO
	 */
	private $_pdo;

	public function __construct(\PDO $pdo)
	{
		$this->_pdo = $pdo;
		$this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function save(\Mp3\FileInfo $FileInfo)
	{
		$this->_pdo->beginTransaction();

		$res = $this->_saveFile($FileInfo);
		if (!$res)
		{
			$this->_pdo->rollBack();
			throw new Exception(sprintf('Couldnt save file %s', $FileInfo->file_path));
		}

		$res = $this->_saveTags($FileInfo->getTags());
		if (!$res) {
			$this->_pdo->rollBack();
			throw new Exception(sprintf('Couldnt save tags for file %s', $FileInfo->file_path));
		}

		$this->_pdo->commit();

		return true;
	}

	public function getAll()
	{
		$sql = '
		SELECT
			f.file_id,
			f.file_path,
			f.time,
			f.frames,
			f.sample_rate,
			f.bitrate,
			f.bitrate_type,
			t.artist,
			t.album,
			t.title,
			t.year,
			t.genre,
			t.track,
			t.tracks,
			t.other
		FROM
			files f
			INNER JOIN tags t
				ON f.file_id = t.file_id';
		$Files = [];
		$stmt = $this->_pdo->query($sql);
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC))
		{
			$Files[] = $this->_createObject($row);
		}

		return $Files;
	}

	private function _createObject(array $row)
	{
		$other = $row['other'];
		unset($row['other']);
		$File = new \Mp3\FileInfo($row['file_path']);
		unset($row['file_path']);
		foreach ($row as $param => $val)
		{
			$File->{$param} = $val;
		}
		if (!empty($other))
		{
			$other = json_decode($other);
			$File->other = $other;
		}
		return $File;
	}

	private function _saveFile(\Mp3\FileInfo $File)
	{
		$sql = '
		INSERT INTO music.files
		(
			file_id,
			file_path,
			time,
			frames,
			sample_rate,
			bitrate,
			bitrate_type
		)
		VALUES
		(
			:file_id,
			:file_path,
			:time,
			:frames,
			:sample_rate,
			:bitrate,
			:bitrate_type
		)';

		$stmt = $this->_pdo->prepare($sql);

		$params = $File->toArray();
		$params = $this->_prepareParams($params);

		$res = $stmt->execute($params);

		if (!$res)
		{
			return false;
		}
		$File->file_id = $this->_pdo->lastInsertId();

		return true;
	}

	private function _prepareParams(array $params)
	{
		$keys = array_keys($params);
		$keys = array_map(function($val)
		{
			return ':' . $val;
		}, $keys);
		$params = array_combine($keys, $params);

		return $params;
	}

	private function _saveTags(\Mp3\Tags $Tags)
	{
		$sql = '
		INSERT INTO music.tags
		(
			file_id,
			artist,
			album,
			title,
			year,
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
		)';

		$stmt = $this->_pdo->prepare($sql);

		$params = $Tags->getFile()->file_id;
		$params = $Tags->toArray();

		$params['other'] = empty($params['other']) ? null : json_encode($params['other'], JSON_UNESCAPED_UNICODE);

		$params = $this->_prepareParams($params);

		$res = $stmt->execute($params);
		if (!$res)
		{
			return false;
		}

		return true;
	}
}
