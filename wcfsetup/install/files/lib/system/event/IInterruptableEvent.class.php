<?php

namespace wcf\system\event;

/**
 * Event listeners handling interruptable events may indicate that the code path
 * of the action that fired the event should not proceed.
 *
 * As an example, this may be used to prevent a successful login if an interruptable
 * event is being fired when a user logs in.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 * @deprecated 6.1 use `wcf\event\IInterruptableEvent` instead
 */
interface IInterruptableEvent extends IEvent
{
    /**
     * Indicates that the code path that fired this event should not proceed after
     * this event was handled. The caller is responsible to check the status with
     * `defaultPrevented()`.
     *
     * All event listeners will be invoked, even if an event listener in the middle
     * of the stack calls `preventDefault()`.
     */
    public function preventDefault(): void;

    /**
     * Returns whether preventDefault() was called.
     */
    public function defaultPrevented(): bool;
}
