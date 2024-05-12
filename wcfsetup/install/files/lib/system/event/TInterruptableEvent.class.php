<?php

namespace wcf\system\event;

/**
 * Default implementation for interruptable events.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.5
 * @deprecated 6.1 use `wcf\event\TInterruptableEvent` instead
 */
trait TInterruptableEvent
{
    /**
     * @var bool
     */
    private $defaultPrevented = false;

    /**
     * @see IInterruptableEvent::preventDefault()
     */
    public function preventDefault(): void
    {
        $this->defaultPrevented = true;
    }

    /**
     * @see IInterruptableEvent::defaultPrevented()
     */
    public function defaultPrevented(): bool
    {
        return $this->defaultPrevented;
    }
}
