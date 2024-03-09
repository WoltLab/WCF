<?php

namespace wcf\system\endpoint\event;

use wcf\system\endpoint\IController;
use wcf\system\event\IEvent;

final class ControllerCollecting implements IEvent
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
