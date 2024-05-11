<?php

namespace wcf\system\worker\event;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\event\IEvent;
use wcf\system\worker\RegisteredWorker;

/**
 * Requests the collection of workers that should be included in the list
 * of rebuild workers.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated use `wcf\event\worker\RebuildWorkerCollecting` instead
 */
class RebuildWorkerCollecting implements IEvent
{
    private \SplPriorityQueue $queue;

    public function __construct()
    {
        $this->queue = new \SplPriorityQueue();

        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.rebuildData');

        foreach ($objectTypes as $objectType) {
            $priority = $objectType->nicevalue ? ($objectType->nicevalue * -1) : 0;
            $this->queue->insert(
                new RegisteredWorker($objectType->className, $objectType),
                $priority
            );
        }
    }

    /**
     * Registers a new worker.
     *
     * @param $nicevalue The worker's priority. Lower values indicate earlier execution.
     */
    public function register(string $classname, int $nicevalue): void
    {
        $this->queue->insert(new RegisteredWorker($classname), -$nicevalue);
    }

    /**
     * @return iterable<RegisteredWorker>
     */
    public function getWorkers(): iterable
    {
        yield from clone $this->queue;
    }
}
