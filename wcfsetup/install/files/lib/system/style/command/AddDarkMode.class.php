<?php

namespace wcf\system\style\command;

use wcf\data\style\Style;

/**
 * Adds the dark color scheme to a style.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 * @deprecated  6.2 Use `\wcf\command\style\AddDarkMode` instead.
 */
final class AddDarkMode
{
    public function __construct(
        private readonly Style $style
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\style\AddDarkMode(
            $this->style
        ))();
    }
}
