<?php

namespace wcf\event\endpoint;

use wcf\event\IPsr14Event;
use wcf\system\endpoint\IController;

/**
 * Collects the list of API controllers.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @deprecated 6.3 Event is obsolete because endpoints are loaded automatically based on location.
 */
final class ControllerCollecting implements IPsr14Event
{
    /**
     * @var IController[]
     */
    private array $controllers = [];

    public function register(IController $controller): void
    {
        $this->controllers[] = $controller;
    }

    /**
     * @return IController[]
     */
    public function getControllers(): array
    {
        return $this->controllers;
    }
}
