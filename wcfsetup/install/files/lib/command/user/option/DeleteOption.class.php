<?php

namespace wcf\command\user\option;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\event\user\option\UserOptionDeleted;
use wcf\system\event\EventHandler;

/**
 * Deletes a user option.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class DeleteOption
{
    public function __construct(
        private readonly UserOption $option,
    ) {}

    public function __invoke(): void
    {
        UserOptionBuilder::delete($this->option);

        UserOptionBuilder::resetCache();

        EventHandler::getInstance()->fire(new UserOptionDeleted($this->option));
    }
}
