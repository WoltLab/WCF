<?php

namespace wcf\event\user\option\category;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user option category has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionCategoryCreated implements IPsr14Event
{
    public function __construct(
        public readonly UserOptionCategory $category,
        public readonly UserOptionCategoryBuilder $builder,
    ) {}
}
