<?php

namespace wcf\system\cache\builder;

use wcf\data\event\listener\EventListener;
use wcf\system\cache\eager\EventListenerCache;

/**
 * Caches the event listeners.
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.3 use `EventListenerCache` instead
 */
class EventListenerCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters): array
    {
        $cache = new EventListenerCache()->getCache();

        $listeners = \array_map(
            static fn(array $row) => new EventListener(null, $row),
            $cache['listeners']
        );
        $toListeners = static fn(array $listenerIDs) => \array_map(
            static fn(int $listenerID) => $listeners[$listenerID],
            $listenerIDs
        );

        $data = [
            'actions' => [],
            'inheritedActions' => [],
        ];
        foreach ($cache['actions'] as $environment => $actions) {
            $data['actions'][$environment] = \array_map($toListeners, $actions);
        }
        foreach ($cache['inheritedActions'] as $environment => $classNames) {
            $data['inheritedActions'][$environment] = \array_map(
                static fn(array $eventNames) => \array_map($toListeners, $eventNames),
                $classNames
            );
        }

        return $data;
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        new EventListenerCache()->rebuild();
    }
}
