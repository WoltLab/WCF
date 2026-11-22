<?php

namespace wcf\command\textModule;

use wcf\data\text\module\TextModule;
use wcf\data\text\module\TextModuleBuilder;
use wcf\event\textModule\TextModulesDeleted;
use wcf\event\textModule\TextModulesDeleting;
use wcf\system\event\EventHandler;

/**
 * Deletes text modules.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteTextModules
{
    /**
     * @param TextModule[] $textModules
     */
    public function __construct(
        private readonly array $textModules,
    ) {}

    public function __invoke(): void
    {
        if ($this->textModules === []) {
            return;
        }

        EventHandler::getInstance()->fire(new TextModulesDeleting($this->textModules));

        TextModuleBuilder::deleteAll(\array_values(\array_map(
            static fn(TextModule $textModule) => $textModule->textModuleID,
            $this->textModules
        )));

        EventHandler::getInstance()->fire(new TextModulesDeleted($this->textModules));
    }
}
