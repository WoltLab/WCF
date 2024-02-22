<?php

namespace wcf\system\background\job;

/**
 * This background job is only queued once
 * and is requeued when it has more work to do.
 *
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

    /**
     * Returns a new instance of this job to be queued again.
     * This will reset the fail counter.
     */
    public function newInstance(): static
    {
        return new static();
    }

    /**
     * Returns whether this job should be queued again because it has more to do.
     *
     * @return bool
     */
    abstract public function queueAgain(): bool;

    #[\Override]
    final public function onFinalFailure()
    {
        // onFailure() and onFinalFailure() are called at the same time.
        // Do your stuff in onFailure().
    }

    #[\Override]
    public function retryAfter()
    {
        // change the default value to 60 seconds
        return 60;
    }
}
