<?php

namespace wcf\data\blacklist\status;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\data\DatabaseObject;
use wcf\system\exception\SystemException;
use wcf\system\io\HttpFactory;

/**
 * Represents a blacklist status.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   string  $date       ISO 8601 date (UTC)
 * @property-read   0|1     $delta1     Is `1` if the first delta (00:00-05:59 UTC) of the day has been fetched, otherwise `0`
 * @property-read   0|1     $delta2     Is `1` if the second delta (06:00-11:59 UTC) of the day has been fetched, otherwise `0`
 * @property-read   0|1     $delta3     Is `1` if the third delta (12:00-17:59 UTC) of the day has been fetched, otherwise `0`
 * @property-read   0|1     $delta4     Is `1` if the fourth delta (18:00-23:59 UTC) of the day has been fetched, otherwise `0`
 * @since 5.2
 */
class BlacklistStatus extends DatabaseObject
{
    /**
     * names of the deltas of a day, ordered by their time period
     *
     * @since 6.3
     */
    public const DELTAS = ['delta1', 'delta2', 'delta3', 'delta4'];

    protected static $databaseTableIndexName = 'date';

    protected static $databaseTableIndexIsIdentity = false;

    /**
     * Returns true if the delta for the time period of the UTC hour has already been fetched.
     *
     * @return bool
     */
    public function hasDelta(int $utcHour)
    {
        if ($utcHour < 6) {
            return $this->delta1 !== 0;
        } elseif ($utcHour < 12) {
            return $this->delta2 !== 0;
        } elseif ($utcHour < 18) {
            return $this->delta3 !== 0;
        } else {
            return $this->delta4 !== 0;
        }
    }

    /**
     * Returns true if all values for this date have been fetched.
     *
     * @return bool
     */
    public function isComplete()
    {
        return $this->delta1 !== 0 && $this->delta2 !== 0 && $this->delta3 !== 0 && $this->delta4 !== 0;
    }

    /**
     * Returns a list of all known dates grouped by their ISO date.
     *
     * @return BlacklistStatus[]
     */
    public static function getAll()
    {
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
     * @throws SystemException
     */
    public static function getNextDelta(array $status, ?ClientInterface $client = null)
    {
        if ($client === null) {
            $client = HttpFactory::makeClientWithTimeout(5);
        }

        // Fetch the index file to determine the oldest possible value that can be retrieved.
        $request = new Request(
            'GET',
            'https://assets.woltlab.com/blacklist/index.json',
            [
                'accept-encoding' => 'gzip',
            ]
        );

        try {
            $response = $client->send($request);
        } catch (ClientExceptionInterface $e) {
            \wcf\functions\exception\logThrowable($e);

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = \json_decode((string)$response->getBody(), true, flags: \JSON_THROW_ON_ERROR);

        // The array is ordered from "now" to "14 days ago".
        foreach (\array_reverse($data) as $entry) {
            $date = $entry['date'];
            if (isset($status[$date])) {
                $dateStatus = $status[$date];
                if ($dateStatus->isComplete()) {
                    continue;
                }

                foreach (self::DELTAS as $delta) {
                    if ($entry['files'][$delta] === true && $dateStatus->{$delta} === 0) {
                        return "{$date}/{$delta}.json";
                    }
                }
            } else {
                foreach (self::DELTAS as $delta) {
                    if ($entry['files'][$delta] === true) {
                        return "{$date}/{$delta}.json";
                    }
                }
            }

            // The `full.json` file is not considered for now, because it is very unlikely that none of the
            // delta files are available. Also, it's significant larger than the delta updates and we cannot
            // reliably predict if we're able to import it at all: slow hosts or max_execution_time almost
            // exhausted by other cronjobs.
        }

        return null;
    }
}
