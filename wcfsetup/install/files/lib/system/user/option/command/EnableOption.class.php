<?php

namespace wcf\system\user\option\command;

use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionEditor;

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
        (new UserOptionEditor($this->option))->update([
            'isDisabled' => 0,
        ]);
    }
}
