<?php

namespace wcf\command\user\option\category;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryAction;

/**
 * Deletes a user option category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteOptionCategory
{
    public function __construct(
        private readonly UserOptionCategory $category,
    ) {}

    public function __invoke(): void
    {
        $action = new UserOptionCategoryAction([$this->category], 'delete');
        $action->executeAction();
    }
}
