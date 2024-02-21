<?php

namespace wcf\system\background\job;

use wcf\system\background\BackgroundQueueHandler;

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
abstract class AbstractUniqueBackgroundJob extends AbstractBackgroundJob
{
    /**
     * @inheritDoc
     */
    final public const MAX_FAILURES = 0;

    /**
     * Returns a unique identifier for this job.
     *
     * @return string
     */
    public function identifier(): string
    {
        return static::class;
    }

    #[\Override]
    final public function perform()
    {
        $this->run();
        if ($this->requeue()) {
            BackgroundQueueHandler::getInstance()->enqueueIn($this);
        }
    }

    /**
     * Runs the job.
     */
    abstract protected function run(): void;

    /**
     * Returns whether this job should be queued again because it has more to do.
     *
     * @return bool
     */
    abstract protected function requeue(): bool;

    #[\Override]
    final public function onFinalFailure()
    {
        if ($this->requeue()) {
            BackgroundQueueHandler::getInstance()->enqueueIn($this, $this->retryAfter());
        }
    }
}
