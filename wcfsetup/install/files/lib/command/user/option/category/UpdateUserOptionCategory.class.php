<?php

namespace wcf\command\user\option\category;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryBuilder;
use wcf\event\user\option\category\UserOptionCategoryUpdated;
use wcf\system\cache\builder\UserOptionCacheBuilder;
use wcf\system\event\EventHandler;

/**
 * Updates a user option category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UpdateUserOptionCategory
{
    public function __construct(
        private readonly UserOptionCategoryBuilder $builder,
    ) {}

    public function __invoke(): UserOptionCategory
    {
        $category = $this->builder->update();

        UserOptionCacheBuilder::getInstance()->reset();

        EventHandler::getInstance()->fire(new UserOptionCategoryUpdated(
            $category,
            $this->builder
        ));

        return $category;
    }
}
