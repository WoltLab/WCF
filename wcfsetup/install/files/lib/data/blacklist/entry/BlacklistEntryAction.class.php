<?php

namespace wcf\data\blacklist\entry;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\blacklist\status\BlacklistStatus;
use wcf\data\blacklist\status\BlacklistStatusEditor;
use wcf\system\io\HttpFactory;
use wcf\system\WCF;
use wcf\util\JSON;

/**
 * Executes blacklist entry-related actions.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method BlacklistEntryEditor[] getObjects()
 * @method BlacklistEntryEditor getSingleObject()
 * @since 5.2
 */
class BlacklistEntryAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = BlacklistEntryEditor::class;

    public function import()
    {
        $client = HttpFactory::makeClientWithTimeout(5);

        // Check if we need to import any data at all.
        $status = BlacklistStatus::getAll();
        $nextDelta = BlacklistStatus::getNextDelta($status, $client);
        if ($nextDelta === null) {
            return;
        }

        $request = new Request(
            'GET',
            "https://assets.woltlab.com/blacklist/{$nextDelta}",
            [
                'accept-encoding' => 'gzip',
            ]
        );
        try {
            $response = $client->send($request);
        } catch (ClientExceptionInterface $e) {
            \wcf\functions\exception\logThrowable($e);

            return;
        }

        if ($response->getStatusCode() !== 200) {
            return;
        }

        $data = JSON::decode((string)$response->getBody());
        if (\is_array($data)) {
            $sql = "INSERT INTO             wcf1_blacklist_entry
                                            (type, hash, lastSeen, occurrences)
                    VALUES                  (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE lastSeen = VALUES(lastSeen),
                                            occurrences = VALUES(occurrences)";
            $statement = WCF::getDB()->prepare($sql);

            $lastSeen = \preg_replace('~^(.+)T(.+)Z~', '$1 $2', $data['meta']['end']);

            WCF::getDB()->beginTransaction();
            foreach (['email', 'ipv4', 'ipv6', 'username'] as $type) {
                foreach ($data[$type] as $hash => $occurrences) {
                    $statement->execute([
                        $type,
                        \hex2bin($hash),
                        $lastSeen,
                        \min($occurrences, 32767),
                    ]);
                }
            }
            WCF::getDB()->commitTransaction();

            $blacklistStatus = new BlacklistStatus($data['meta']['date']);
            if (!$blacklistStatus->date) {
                $blacklistStatus = BlacklistStatusEditor::create(['date' => $data['meta']['date']]);
            }

            (new BlacklistStatusEditor($blacklistStatus))->update([$data['meta']['type'] => 1]);
        }
    }
}
