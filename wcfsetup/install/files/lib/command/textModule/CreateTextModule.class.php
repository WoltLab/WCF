<?php

namespace wcf\command\textModule;

use wcf\data\text\module\TextModule;
use wcf\data\text\module\TextModuleBuilder;
use wcf\event\textModule\TextModuleCreated;
use wcf\system\event\EventHandler;

/**
 * Creates a new text module.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateTextModule
{
    public function __construct(
        private readonly TextModuleBuilder $builder,
    ) {}

    public function __invoke(): TextModule
    {
        $textModule = $this->builder->create();

        EventHandler::getInstance()->fire(new TextModuleCreated($textModule, $this->builder));

        return $textModule;
    }
}
