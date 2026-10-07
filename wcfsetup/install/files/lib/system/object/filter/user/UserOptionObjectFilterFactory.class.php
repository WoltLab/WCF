<?php

namespace wcf\system\object\filter\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\object\filter\IObjectListFilter;
use wcf\system\option\user\TUserOptionsByCategory;

/**
 * Creates the object filters for the user options of the profile.
 *
 * Disabled options are included as well, their filters are unavailable for
 * new filters, but stored filters remain effective.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionObjectFilterFactory
{
    use TUserOptionsByCategory;

    /**
     * @return list<IObjectListFilter<User, mixed>>
     */
    public function getFilters(): array
    {
        $optionsByCategory = $this->getUserOptionsByCategory(
            'profile',
            static fn(UserOption $option) => $option->getFormOption()->isFilterable()
        );

        $filters = [];
        foreach ($optionsByCategory as $options) {
            foreach ($options as $option) {
                $filters[] = match ($option->optionType) {
                    'birthday' => new UserOptionAgeObjectFilter($option),
                    default => new UserOptionObjectFilter($option),
                };
            }
        }

        return $filters;
    }
}
