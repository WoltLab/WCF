<?php

namespace wcf\command\blacklist;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\data\blacklist\entry\BlacklistEntry;
use wcf\data\blacklist\status\BlacklistStatus;
use wcf\data\blacklist\status\BlacklistStatusBuilder;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;

use function wcf\functions\exception\logThrowable;

/**
 * Imports the next pending delta of the built-in blacklist data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @phpstan-type Entries array{
 *  meta: array{
 *   date: string,
 *   end: string,
 *   start: string,
 *   type: string
 *  },
 *  ipv4: array<string, int>,
 *  ipv6: array<string, int>,
 *  email: array<string, int>,
 *  username: array<string, int>,
 * }
 */
final class ImportBlacklist
{
    private readonly ClientInterface $client;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?? HttpFactory::makeClientWithTimeout(5);
    }

    public function __invoke(): void
    {
        // Check if we need to import any data at all.
        $status = BlacklistStatus::getAll();
        $nextDelta = BlacklistStatus::getNextDelta($status, $this->client);
        if ($nextDelta === null) {
            return;
        }

        $data = $this->fetchDelta($nextDelta);
        if ($data === null) {
            return;
        }

        $this->saveEntries($data);

        $this->updateStatus($data['meta']['date'], $data['meta']['type']);
    }

    /**
     * @return ?Entries
     */
    private function fetchDelta(string $delta): ?array
    {
        $request = new Request(
            'GET',
            "https://assets.woltlab.com/blacklist/{$delta}",
            [
                'accept-encoding' => 'gzip',
            ]
        );
        try {
            $response = $this->client->send($request);
        } catch (ClientExceptionInterface $e) {
            logThrowable($e);

            return null;
        }

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        return \json_decode((string)$response->getBody(), true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * @param Entries $data
     */
    private function saveEntries(array $data): void
    {
        $sql = "INSERT INTO             wcf1_blacklist_entry
                                        (type, hash, lastSeen, occurrences)
                VALUES                  (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE lastSeen = VALUES(lastSeen),
                                        occurrences = VALUES(occurrences)";
        $statement = WCF::getDB()->prepare($sql);

        $lastSeen = new \DateTimeImmutable($data['meta']['end']);

        WCF::getDB()->beginTransaction();
        foreach (BlacklistEntry::TYPES as $type) {
            foreach ($data[$type] as $hash => $occurrences) {
                $statement->execute([
                    $type,
                    \hex2bin($hash),
                    $lastSeen->format('Y-m-d H:i:s'),
                    \min($occurrences, BlacklistEntry::MAX_OCCURRENCES),
                ]);
            }
        }
        WCF::getDB()->commitTransaction();
    }

    private function updateStatus(string $date, string $delta): void
    {
        $blacklistStatus = new BlacklistStatus($date);
        if ($blacklistStatus->isNil()) {
            $blacklistStatus = BlacklistStatusBuilder::forCreate()
                ->setDate($date)
                ->create();
        }

        BlacklistStatusBuilder::forUpdate($blacklistStatus)
            ->setDelta($delta)
            ->update();
    }
}
