<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_osm
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

class Utility
{
	public static function isNowBetweenDates($startDateStr, $endDateStr) {
		$now = date('Y-m-d');
		$startDate = date('Y-m-d', strtotime($startDateStr));
		$endDate = date('Y-m-d', strtotime($endDateStr));
		if (($now >= $startDate) && ($now <= $endDate)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public static function isDateInTheFuture($dateStr) {
		$now = date('Y-m-d');
		$date = date('Y-m-d', strtotime($dateStr));
		if ($now <= $date) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

class DatabaseHelper
{
	// returns whether a record for the given URL is already in the database
	public static function urlExists($url) {
		// Obtain a database connection
		$db = JFactory::getDbo();
		// Retrieve url
		$query = $db->getQuery(true)
					->select($db->quoteName('response'))
					->from($db->quoteName('#__mod_osm'))
					->where('url = ' . $db->quote($url));
		// Prepare the query
		$db->setQuery($query);
		// Load the row.
		$result = $db->loadResult();

		if (empty($result)) {
			return FALSE;
		}

		return TRUE;
	}

	// reads from the database
	public static function readRow($url) {
		// Obtain a database connection
		$db = JFactory::getDbo();
		// Retrieve url
		$query = $db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__mod_osm'))
					->where('url = ' . $db->quote($url));
		// Prepare the query
		$db->setQuery($query);
		// Load the row.
		$result = $db->loadObject();
		return $result;
	}

	public static function writeRow($url, $response) {
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// insert or update?
		if (DatabaseHelper::urlExists($url)) {
			// Fields to update.
			$now = date_create()->format('Y-m-d H:i:s');
			$fields = array(
				$db->quoteName('fetched_at') . ' = ' . $db->quote($now),
				$db->quoteName('response') . ' = ' . $db->quote($response)
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('url') . ' = ' . $db->quote($url)
			);

			$query
				->update($db->quoteName('#__mod_osm'))
				->set($fields)
				->where($conditions);
		} else {
			// Columns.
			$columns = array('url', 'fetched_at', 'response');

			// Insert values.
			$now = date_create()->format('Y-m-d H:i:s');
			$values = array($db->quote($url), $db->quote($now), $db->quote($response));

			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__mod_osm'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
		}

		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);
		$db->execute();
	}
}

/**
 * Helper for mod_osm
 */
class OSMHelper
{
	private const TOKEN_TIMEOUT = 3000;
	private const TIMEOUT = 60;

	private $clientId = NULL;
	private $clientSecret = NULL;
	private $incEvents = FALSE;
	private $accessToken = FALSE;

	function __construct($clientId, $clientSecret, $incEvents = FALSE) {
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->incEvents = $incEvents;
		$this->setToken();
	}

	// returns the number of seconds between DateTime objects $start and $end
	private function compareDates($start, $end) {
		$diff = $start->diff($end);

		$daysInSecs = $diff->format('%r%a') * 24 * 60 * 60;
		$hoursInSecs = $diff->h * 60 * 60;
		$minsInSecs = $diff->i * 60;

		$seconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;

		return $seconds;
	}

	// sets the access token, returns FALSE on failure
	private function setTokenFromOSM($url) {
		// protection from trying to get a token before the module is configured
		if (empty($this->clientId) || empty($this->clientSecret)) {
			return FALSE;
		}

		// standard scope is read-only for the programme
		$scope = 'section:programme:read';
		if ($this->incEvents) {
			// additionally we could support a read-only scope for events
			$scope .= ' section:event:read';
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$post = array(
			'grant_type' => 'client_credentials',
			'scope' => $scope,
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->clientSecret);

		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			return FALSE;
		}
		curl_close($ch);

		$json = json_decode($response);
		DatabaseHelper::writeRow($url, $response);
		$this->accessToken = $json->{'access_token'};

		return TRUE;
	}

	// sets the access token either via the database or by OSM
	private function setToken() {
		// OSM access token authentication URL
		$url = 'https://www.onlinescoutmanager.co.uk/oauth/token';

		// if there's already a token in the database
		if (DatabaseHelper::urlExists($url)) {
			$tokenRow = DatabaseHelper::readRow($url);
			$now = new DateTime();
			$fetchedAt = new DateTime($tokenRow->fetched_at);
			if ($this->compareDates($fetchedAt, $now) > self::TOKEN_TIMEOUT) {
				// get a new token from OSM if we need to
				return $this->setTokenFromOSM($url);
			} else {
				// set token from DB
				$tokenResponseJson = json_decode($tokenRow->response);
				$this->accessToken = $tokenResponseJson->{'access_token'};
				return TRUE;
			}

		} else {
			// if there's no token in the DB, get one from OSM
			return $this->setTokenFromOSM($url);
		}
	}

	// return the access token
	public function getToken() {
		return $this->accessToken;
	}

	// returns JSON from the given URL or FALSE on failure
	private function getJsonFromOSM($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		$headers = array();
		$headers[] = "Authorization: Bearer $this->accessToken";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			return FALSE;
		}
		curl_close($ch);

		DatabaseHelper::writeRow($url, $response);

		return $response;
	}

	private function getJson($url, $timeout = self::TIMEOUT) {
		// if there's already a response in the database
		if (DatabaseHelper::urlExists($url)) {
			$responseRow = DatabaseHelper::readRow($url);
			$now = new DateTime();
			$fetchedAt = new DateTime($responseRow->fetched_at);
			if ($this->compareDates($fetchedAt, $now) > $timeout) {
				// get a new response from OSM if we need to
				$response = $this->getJsonFromOSM($url);
			} else {
				// set response from DB
				$response = $responseRow->response;
			}

		} else {
			// get a new response from OSM if we need to
			$response = $this->getJsonFromOSM($url);
		}
		return $response;
	}

	public function getResource() {
		// the resoure response doesn't change very often so set a higher timeout for this
		$response = $this->getJson('https://www.onlinescoutmanager.co.uk/oauth/resource', 3600);
		return json_decode($response);
	}

	public function getProgramme($sectionId, $termId) {
		$response = $this->getJson('https://www.onlinescoutmanager.co.uk/ext/programme/?action=getProgrammeSummary&sectionid=' . $sectionId . '&termid=' . $termId);
		return json_decode($response);
	}
}
