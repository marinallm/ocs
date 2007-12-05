<?php

/**
 * @file TrackDAO.inc.php
 *
 * Copyright (c) 2000-2007 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package conference
 * @class TrackDAO
 *
 * Class for track DAO.
 * Operations for retrieving and modifying Track objects.
 *
 * $Id$
 */

import ('conference.Track');

class TrackDAO extends DAO {
	/**
	 * Retrieve a track by ID.
	 * @param $trackId int
	 * @return Track
	 */
	function &getTrack($trackId) {
		$result = &$this->retrieve(
			'SELECT * FROM tracks WHERE track_id = ?', $trackId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnTrackFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a track by abbreviation.
	 * @param $trackAbbrev string
	 * @param $locale string Optional
	 * @return track
	 */
	function &getTrackByAbbrev($trackAbbrev, $schedConfId, $locale = null) {
		$sql = 'SELECT * FROM tracks t, track_settings l WHERE l.track_id = t.track_id AND l.setting_name = ? AND l.setting_value = ? AND t.sched_conf_id = ?';
		$params = array('abbrev', $trackAbbrev, $schedConfId);

		if ($locale !== null) {
			$sql .= ' AND l.locale = ?';
			$params[] = $locale;
		}
		$result = &$this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnTrackFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a track by title.
	 * @param $trackTitle string
	 * @param $locale string optional
	 * @return track
	 */
	function &getTrackByTitle($trackTitle, $schedConfId, $locale = null) {
		$sql = 'SELECT * FROM tracks t, track_settings l WHERE l.track_id = t.track_id AND l.setting_name = ? AND l.setting_value = ? AND t.sched_conf_id = ?';
		$params = array('title', $trackTitle, $schedConfId);

		if ($locale !== null) {
			$sql .= ' AND l.locale = ?';
			$params[] = $locale;
		}
		$result = &$this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnTrackFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a track by title and abbrev.
	 * @param $trackTitle string
	 * @param $trackAbbrev string
	 * @param $locale string optional
	 * @return Track
	 */
	function &getTrackByTitleAndAbbrev($trackTitle, $trackAbbrev, $schedConfId, $locale = null) {
		$params = array('title', 'abbrev', $trackTitle, $trackAbbrev, $schedConfId);
		if ($locale !== null) {
			$params[] = $locale;
			$params[] = $locale;
		}

		$sql = 'SELECT	t.*
			FROM	tracks t,
				track_settings l1,
				track_settings l2
			WHERE	l1.track_id = t.track_id AND
				l2.track_id = t.track_id AND
				l1.setting_name = ? AND
				l2.setting_name = ? AND
				l1.setting_value = ? AND
				l2.setting_value = ? AND
				t.sched_conf_id = ?';
		if ($locale !== null) $sql .= ' AND l1.locale = ? AND l2.locale = ?';
		$result =& $this->retrieve($sql, $params);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnTrackFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a Track object from a row.
	 * @param $row array
	 * @return Track
	 */
	function &_returnTrackFromRow(&$row) {
		$track = &new Track();
		$track->setTrackId($row['track_id']);
		$track->setSchedConfId($row['sched_conf_id']);
		$track->setSequence($row['seq']);
		$track->setMetaReviewed($row['meta_reviewed']);
		$track->setDirectorRestricted($row['director_restricted']);

		$this->getDataObjectSettings('track_settings', 'track_id', $row['track_id'], $track);

		HookRegistry::call('TrackDAO::_returnTrackFromRow', array(&$track, &$row));

		return $track;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title', 'abbrev', 'policy', 'identifyType');
	}

	/**
	 * Update the localized fields for this table
	 * @param $track object
	 */
	function updateLocaleFields(&$track) {
		$this->updateDataObjectSettings('track_settings', $track, array(

			'track_id' => $track->getTrackId()
		));
	}

	/**
	 * Insert a new track.
	 * @param $track Track
	 */	
	function insertTrack(&$track) {
		$this->update(
			'INSERT INTO tracks
				(sched_conf_id, seq, meta_reviewed, director_restricted)
				VALUES
				(?, ?, ?, ?)',
			array(
				$track->getSchedConfId(),
				$track->getSequence() == null ? 0 : $track->getSequence(),
				$track->getMetaReviewed() ? 1 : 0,
				$track->getDirectorRestricted() ? 1 : 0
			)
		);

		$track->setTrackId($this->getInsertTrackId());
		$this->updateLocaleFields($track);
		return $track->getTrackId();
	}

	/**
	 * Update an existing track.
	 * @param $track Track
	 */
	function updateTrack(&$track) {
		$returner = $this->update(
			'UPDATE tracks
				SET
					seq = ?,
					meta_reviewed = ?,
					director_restricted = ?
				WHERE track_id = ?',
			array(
				$track->getSequence(),
				$track->getMetaReviewed(),
				$track->getDirectorRestricted(),
				$track->getTrackId()
			)
		);
		$this->updateLocaleFields($track);
		return $returner;
	}

	/**
	 * Delete a track.
	 * @param $track Track
	 */
	function deleteTrack(&$track) {
		return $this->deleteTrackById($track->getTrackId(), $track->getSchedConfId());
	}

	/**
	 * Delete a track by ID.
	 * @param $trackId int
	 * @param $schedConfId int optional
	 */
	function deleteTrackById($trackId, $schedConfId = null) {
		$trackDirectorsDao = &DAORegistry::getDAO('TrackDirectorsDAO');
		$trackDirectorsDao->deleteDirectorsByTrackId($trackId, $schedConfId);

		// Remove papers from this track
		$paperDao = &DAORegistry::getDAO('PaperDAO');
		$paperDao->removePapersFromTrack($trackId);

		// Delete published paper entries from this track -- they must
		// be re-published.
		$publishedPaperDao = &DAORegistry::getDAO('PublishedPaperDAO');
		$publishedPaperDao->deletePublishedPapersByTrackId($trackId);

		if (isset($schedConfId) && !$this->trackExists($trackId, $schedConfId)) return false;
		$this->update('DELETE FROM track_settings WHERE track_id = ?', array($trackId));
		return $this->update('DELETE FROM tracks WHERE track_id = ?', array($trackId));
	}

	/**
	 * Delete tracks by sched conf ID including ALL dependents.
	 * @param $schedConfId int
	 */
	function deleteTracksBySchedConf($schedConfId) {
		$tracks =& $this->getSchedConfTracks($schedConfId);
		while (($track =& $tracks->next())) {
			$this->deleteTrack($track);
			unset($track);
		}
	}

	/**
	 * Retrieve an array associating all track director IDs with 
	 * arrays containing the tracks they edit.
	 * @return array directorId => array(tracks they edit)
	 */
	function &getDirectorTracks($schedConfId) {
		$returner = array();

		$result = &$this->retrieve(
			'SELECT t.*, td.user_id AS director_id FROM track_directors td, tracks t WHERE td.track_id = t.track_id AND t.sched_conf_id = td.sched_conf_id AND t.sched_conf_id = ?',
			$schedConfId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$track = &$this->_returnTrackFromRow($row);
			if (!isset($returner[$row['director_id']])) {
				$returner[$row['director_id']] = array($track);
			} else {
				$returner[$row['director_id']][] = $track;
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all tracks in which papers are currently published in
	 * the given scheduled conference.
	 * @return array
	 */
	function &getTracksBySchedConfId($schedConfId) {
		$returner = array();

		$result = &$this->retrieve(
			'SELECT DISTINCT t.*,
				COALESCE(o.seq, t.seq) AS track_seq
			FROM tracks t, papers p
			LEFT JOIN sched_confs i ON (p.sched_conf_id = i.sched_conf_id) AND (i.sched_conf_id = ?)
			LEFT JOIN custom_track_orders o ON (p.track_id = o.track_id AND o.sched_conf_id = i.sched_conf_id)
			WHERE t.track_id = p.track_id ORDER BY track_seq',
			array($schedConfId)
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[] = &$this->_returnTrackFromRow($row);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve all tracks for a scheduled conference.
	 * @return DAOResultFactory containing Tracks ordered by sequence
	 */
	function &getSchedConfTracks($schedConfId, $rangeInfo = null) {
		$result = &$this->retrieveRange(
			'SELECT * FROM tracks WHERE sched_conf_id = ? ORDER BY seq',
			$schedConfId, $rangeInfo
		);

		$returner = &new DAOResultFactory($result, $this, '_returnTrackFromRow');
		return $returner;
	}

	/**
	 * Retrieve the IDs and titles of the tracks for a scheduled conference in an associative array.
	 * @return array
	 */
	function &getTrackTitles($schedConfId, $submittableOnly = false) {
		$tracks = array();
		$tracksIterator =& $this->getSchedConfTracks($schedConfId);
		while (($track =& $tracksIterator->next())) {
			if (!$submittableOnly || !$track->getDirectorRestricted()) {
				$tracks[$track->getTrackId()] = $track->getTrackTitle();
			}

			unset($track);
		}

		return $tracks;
	}

	/**
	 * Check if a track exists with the specified ID.
	 * @param $trackId int
	 * @param $schedConfId int
	 * @return boolean
	 */
	function trackExists($trackId, $schedConfId) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) FROM tracks WHERE track_id = ? AND sched_conf_id = ?',
			array($trackId, $schedConfId)
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Sequentially renumber tracks in their sequence order.
	 * @param $schedConfId int
	 */
	function resequenceTracks($schedConfId) {
		$result = &$this->retrieve(
			'SELECT track_id FROM tracks WHERE sched_conf_id = ? ORDER BY seq',
			$schedConfId
		);

		for ($i=1; !$result->EOF; $i++) {
			list($trackId) = $result->fields;
			$this->update(
				'UPDATE tracks SET seq = ? WHERE track_id = ?',
				array(
					$i,
					$trackId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the ID of the last inserted track.
	 * @return int
	 */
	function getInsertTrackId() {
		return $this->getInsertId('tracks', 'track_id');
	}

	/**
	 * Delete the custom ordering of an scheduled conference's tracks.
	 * @param $schedConfId int
	 */
	function deleteCustomTrackOrdering($schedConfId) {
		return $this->update(
			'DELETE FROM custom_track_orders WHERE sched_conf_id = ?', $schedConfId
		);
	}

	/**
	 * Sequentially renumber custom track orderings in their sequence order.
	 * @param $schedConfId int
	 */
	function resequenceCustomTrackOrders($schedConfId) {
		$result = &$this->retrieve(
			'SELECT track_id FROM custom_track_orders WHERE sched_conf_id = ? ORDER BY seq',
			$schedConfId
		);

		for ($i=1; !$result->EOF; $i++) {
			list($trackId) = $result->fields;
			$this->update(
				'UPDATE custom_track_orders SET seq = ? WHERE track_id = ? AND sched_conf_id = ?',
				array(
					$i,
					$trackId,
					$schedConfId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Check if a scheduled conference has custom track ordering.
	 * @param $schedConfId int
	 * @return boolean
	 */
	function customTrackOrderingExists($schedConfId) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) FROM custom_track_orders WHERE sched_conf_id = ?',
			$schedConfId
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 0 ? false : true;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get the custom track order of a track.
	 * @param $schedConfId int
	 * @param $trackId int
	 * @return int
	 */
	function getCustomTrackOrder($schedConfId, $trackId) {
		$result = &$this->retrieve(
			'SELECT seq FROM custom_track_orders WHERE sched_conf_id = ? AND track_id = ?',
			array($schedConfId, $trackId)
		);

		$returner = null;
		if (!$result->EOF) {
			list($returner) = $result->fields;
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Import the current track orders into the specified scheduled conference as custom
	 * scheduled conference orderings.
	 * @param $schedConfId int
	 */
	function setDefaultCustomTrackOrders($schedConfId) {
		$result = &$this->retrieve(
			'SELECT t.track_id FROM tracks t, sched_confs i WHERE i.sched_conf_id = t.sched_conf_id AND i.sched_conf_id = ? ORDER BY seq',
			$schedConfId
		);

		for ($i=1; !$result->EOF; $i++) {
			list($trackId) = $result->fields;
			$this->_insertCustomTrackOrder($schedConfId, $trackId, $i);
			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * INTERNAL USE ONLY: Insert a custom track ordering
	 * @param $schedConfId int
	 * @param $trackId int
	 * @param $seq int
	 */
	function _insertCustomTrackOrder($schedConfId, $trackId, $seq) {
		$this->update(
			'INSERT INTO custom_track_orders (track_id, sched_conf_id, seq) VALUES (?, ?, ?)',
			array(
				$trackId,
				$schedConfId,
				$seq
			)
		);
	}

	/**
	 * Move a custom scheduled conference ordering up or down, resequencing as necessary.
	 * @param $schedConfId int
	 * @param $trackId int
	 * @param $newPos int The new position (0-based) of this track
	 * @param $up boolean Whether we're moving the track up or down
	 */
	function moveCustomTrackOrder($schedConfId, $trackId, $newPos, $up) {
		$this->update(
			'UPDATE custom_track_orders SET seq = ? ' . ($up?'-':'+') . ' 0.5 WHERE sched_conf_id = ? AND track_id = ?',
			array($newPos, $schedConfId, $trackId)
		);
		$this->resequenceCustomTrackOrders($schedConfId);
	}
}

?>
