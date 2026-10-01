<?php

namespace wcf\command\user\option;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\event\user\option\UserOptionEnabled;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Enables a user option.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class EnableOption
{
    public function __construct(
        private readonly UserOption $option,
    ) {}

    public function __invoke(): void
    {
        UserOptionBuilder::forUpdate($this->option)
            ->setIsDisabled(false)
            ->update();

        UserOptionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new UserOptionEnabled($this->option));
    }
}
