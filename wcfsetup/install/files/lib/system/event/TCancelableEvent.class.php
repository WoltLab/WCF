<?php

namespace wcf\system\event;

/**
 * Default implementation for cancelable events.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event
 * @since   5.5
 */
trait TCancelableEvent
{
    /**
     * @var bool
     */
    private $isCancelled = false;

    /**
     * @see ICancelableEvent::cancel()
     */
    public function cancel(): void
    {
        $this->isCancelled = true;
    }

    /**
     * @see ICancelableEvent::isCancelled()
     */
    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }
}
