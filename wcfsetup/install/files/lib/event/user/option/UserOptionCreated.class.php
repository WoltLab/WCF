<?php

namespace wcf\event\user\option;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionBuilder;
use wcf\event\IPsr14Event;

/**
 * Indicates that a user option has been created.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserOptionCreated implements IPsr14Event
{
    public function __construct(
        public readonly UserOption $option,
        public readonly UserOptionBuilder $builder,
    ) {}
}
