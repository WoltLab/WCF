<?php

namespace wcf\command\user\option;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\event\user\option\UserOptionUpdated;
use wcf\system\event\EventHandler;

/**
 * Updates a user option.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateOption
{
    public function __construct(
        private readonly UserOptionBuilder $builder,
    ) {}

    public function __invoke(): UserOption
    {
        $option = $this->builder->update();

        UserOptionBuilder::resetCache();

        EventHandler::getInstance()->fire(new UserOptionUpdated($option, $this->builder));

        return $option;
    }
}
