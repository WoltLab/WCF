<?php

namespace wcf\system\event;

/**
 * Indicates that the event may be cancelled, stopping the action from happening.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Event
 * @since   5.5
 */
interface ICancelableEvent extends IEvent
{
    /**
     * Cancels the event.
     */
    public function cancel(): void;

    /**
     * Returns whether the event is cancelled.
     */
    public function isCancelled(): bool;
}
