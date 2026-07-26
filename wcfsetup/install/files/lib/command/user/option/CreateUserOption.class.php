<?php

namespace wcf\command\user\option;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\event\user\option\UserOptionCreated;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Creates a new user option.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CreateUserOption
{
    public function __construct(
        private readonly UserOptionBuilder $builder,
    ) {}

    public function __invoke(): UserOption
    {
        $option = $this->builder->create();

        UserOptionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new UserOptionCreated(
            $option,
            $this->builder
        ));

        return $option;
    }
}
