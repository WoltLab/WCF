<?php
namespace wcf\data\blacklist\status;
use wcf\data\DatabaseObject;
use wcf\system\exception\HTTPNotFoundException;
use wcf\system\exception\HTTPServerErrorException;
use wcf\system\exception\HTTPUnauthorizedException;
use wcf\system\exception\SystemException;
use wcf\util\exception\HTTPException;
use wcf\util\HTTPRequest;
use wcf\util\JSON;

/**
 * Represents a blacklist status.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Blacklist\Status
 * 
 * @property-read string $date ISO 8601 date (UTC)
 * @property-read int $delta1 Is 1 if the first delta (00:00-05:59 UTC) of the day has been fetched, otherwise 0
 * @property-read int $delta2 Is 1 if the second delta (06:00-11:59 UTC) of the day has been fetched, otherwise 0
 * @property-read int $delta3 Is 1 if the third delta (12:00-17:59 UTC) of the day has been fetched, otherwise 0
 * @property-read int $delta4 Is 1 if the fourth delta (18:00-23:59 UTC) of the day has been fetched, otherwise 0
 * @since 5.2
 */
class BlacklistStatus extends DatabaseObject {
	protected static $databaseTableIndexName = 'date';
	protected static $databaseTableIndexIsIdentity = false;
	
	/**
	 * Returns true if the delta for the time period of the UTC hour has already been fetched.
	 * 
	 * @param int $utcHour
	 * @return bool
	 */
	public function hasDelta($utcHour) {
		if ($utcHour < 6) return !!$this->delta1;
		else if ($utcHour < 12) return !!$this->delta2;
		else if ($utcHour < 18) return !!$this->delta3;
		else return !!$this->delta4;
	}
	
	/**
	 * Returns true if all values for this date have been fetched.
	 * 
	 * @return bool
	 */
	public function isComplete() {
		return $this->delta1 && $this->delta2 && $this->delta3 && $this->delta4;
	}
	
	/**
	 * Returns a list of all known dates grouped by their ISO date.
	 * 
	 * @return BlacklistStatus[]
	 */
	public static function getAll() {
		$objectList = new BlacklistStatusList();
		$objectList->readObjects();
		
		$status = [];
		foreach ($objectList as $blacklistStatus) {
			$status[$blacklistStatus->date] = $blacklistStatus;
		}
		
		return $status;
	}
	
	/**
	 * Evaluates the available and already processed delta update in order to determine the
	 * next delta update. Returns the relative filename or `null` if there is no update or
	 * an error occurred.
	 * 
	 * @param BlacklistStatus[] $status
	 * @return string|null
	 */
	public static function getNextDelta(array $status) {
		// Fetch the index file to determine the oldest possible value that can be retrieved.
		$request = new HTTPRequest('https://assets.woltlab.com/blacklist/index.json');
		try {
			$request->execute();
		}
		catch (SystemException $e) {
			if (
				$e instanceof HTTPNotFoundException
				|| $e instanceof HTTPUnauthorizedException
				|| $e instanceof HTTPServerErrorException
				|| $e instanceof HTTPException
			) {
				\wcf\functions\exception\logThrowable($e);
				
				return null;
			}
			
			throw $e;
		}
		
		$response = $request->getReply();
		if ($response['statusCode'] == 200) {
			$data = JSON::decode($response['body']);
			if (is_array($data)) {
				$deltas = ['delta1', 'delta2', 'delta3', 'delta4'];
				
				// The array is ordered from "now" to "14 days ago".
				foreach (array_reverse($data) as $entry) {
					$date = $entry['date'];
					if (isset($status[$date])) {
						$dateStatus = $status[$date];
						if ($dateStatus->isComplete()) {
							continue;
						}
						
						foreach ($deltas as $delta) {
							if ($entry['files'][$delta] && !$dateStatus->{$delta}) {
								return "{$date}/{$delta}.json";
							}
						}
					}
					else {
						foreach ($deltas as $delta) {
							if ($entry['files'][$delta]) {
								return "{$date}/{$delta}.json";
							}
						} 
					}
					
					// The `full.json` file is not considered for now, because it is very unlikely that none of the
					// delta files are available. Also, it's significant larger than the delta updates and we cannot
					// reliably predict if we're able to import it at all: slow hosts or max_execution_time almost
					// exhausted by other cronjobs.
				}
			}
		}
		
		return null;
	}
}
