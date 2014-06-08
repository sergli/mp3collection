<?php

class Mp3FileMapper
{
	private $_pdo;

	public function __construct(PDO $pdo) {
		$this->_pdo = $pdo;
		$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function save(Mp3File $Mp3File) {

		$this->_pdo->beginTransaction();

		$res = $this->_saveFile($Mp3File);
		if (!$res) {
			$this->_pdo->rollBack();
			throw new Exception(sprintf('Couldnt save file %s', $Mp3File->file_path));
		}

		$res = $this->_saveTags($Mp3File->getTags());
		if (!$res) {
			$this->_pdo->rollBack();
			throw new Exception(sprintf('Couldnt save tags for file %s', $Mp3File->file_path));
		}

		$this->_pdo->commit();

		return true;
	}

	public function getAll() {
		$sql = '
		SELECT
			f.file_id,
			f.file_path,
			f.time,
			f.frames,
			f.sample_rate,
			f.mpeg_version,
			f.layer,
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
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$File = $this->_createObject($row);
			if ($File) {
				$Files[] = $File;
			}
		}

		return $Files;
	}

	private function _createObject(array $row) {


	}


	private function _saveFile(Mp3File $File) {
		$sql = '
		INSERT INTO music.files
		(
			file_id,
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
			:file_id,
			:file_path,
			:time,
			:frames,
			:sample_rate,
			:mpeg_version,
			:layer,
			:bitrate,
			:bitrate_type
		)';

		$stmt = $this->_pdo->prepare($sql);

		$params = $File->toArray();
		$params = $this->_prepareParams($params);

		$res = $stmt->execute($params);

		if (!$res) {
			return false;
		}
		$File->file_id = $this->_pdo->lastInsertId();

		return true;
	}

	private function _prepareParams(array $params) {
		$keys = array_keys($params);
		$keys = array_map(function($val) {
			return ':' . $val;
		}, $keys);
		$params = array_combine($keys, $params);

		return $params;
	}

	private function _saveTags(Mp3Tags $Tags) {
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
		$params = $this->_prepareParams($params);

		$res = $stmt->execute($params);
		if (!$res) {
			return false;
		}

		return true;
	}
}
