<?php

namespace wcf\event\user\group\option;

use wcf\data\user\group\option\UserGroupOption;
use wcf\event\IPsr14Event;

/**
 * Indicates that the values of a user group option have been updated.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserGroupOptionValuesUpdated implements IPsr14Event
{
    public function __construct(
        public readonly UserGroupOption $option,
        /** @var array<int, int|float|string> */
        public readonly array $groupIDToValue
    ) {}
}
