<?php

namespace wcf\system\cache\eager;

use wcf\data\event\listener\EventListener;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Eager cache of the database rows of the legacy event listeners and their
 * assignment to events per environment. It consists of plain arrays only, which
 * allows the OPcache to keep it in shared memory, the `EventListener` objects
 * are created on demand by `EventHandler`.
 *
 * Important: `EventListenerList` cannot be used here, because
 * `DatabaseObjectList` fires an event.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type EventListenerRow array{
 *     listenerID: int,
 *     packageID: int,
 *     environment: 'user'|'admin'|'all',
 *     listenerName: string,
 *     eventClassName: string,
 *     eventName: ?string,
 *     listenerClassName: string,
 *     inherit: int,
 *     niceValue: int,
 *     permissions: ?string,
 *     options: ?string,
 * }
 *
 * @extends AbstractEagerCache<array{
 *     listeners: array<int, EventListenerRow>,
 *     actions: array<'admin'|'user', array<string, list<int>>>,
 *     inheritedActions: array<'admin'|'user', array<string, array<string, list<int>>>>,
 * }>
 */
final class EventListenerCache extends AbstractEagerCache
{
    #[\Override]
    protected function getCacheData(): array
    {
        $data = [
            'listeners' => [],
            'actions' => [
                'admin' => [],
                'user' => [],
            ],
            'inheritedActions' => [
                'admin' => [],
                'user' => [],
            ],
        ];

        $sql = "SELECT      *
                FROM        wcf1_event_listener
                ORDER BY    niceValue ASC, listenerClassName ASC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $listenerID = $row['listenerID'];
            $data['listeners'][$listenerID] = $row;

            $eventNames = new EventListener(null, $row)->getEventNames();
            $environments = $row['environment'] === 'all' ? ['admin', 'user'] : [$row['environment']];

            foreach ($environments as $environment) {
                foreach ($eventNames as $eventName) {
                    if ($row['inherit'] === 0) {
                        $key = EventHandler::generateKey($row['eventClassName'], $eventName);
                        $data['actions'][$environment][$key][] = $listenerID;
                    } else {
                        $data['inheritedActions'][$environment][$row['eventClassName']][$eventName][] = $listenerID;
                    }
                }
            }
        }

        return $data;
    }
}
