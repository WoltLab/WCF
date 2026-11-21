<?php

namespace wcf\command\textModule;

use wcf\data\text\module\TextModule;
use wcf\data\text\module\TextModuleBuilder;
use wcf\event\textModule\TextModuleUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a text module.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateTextModule
{
    public function __construct(
        private readonly TextModuleBuilder $builder,
    ) {}

    public function __invoke(): TextModule
    {
        $textModule = $this->builder->update();

        EventHandler::getInstance()->fire(new TextModuleUpdated($textModule, $this->builder));

        return $textModule;
    }
}
