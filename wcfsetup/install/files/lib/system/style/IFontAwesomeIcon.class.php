<?php

namespace wcf\system\style;

use wcf\system\style\exception\InvalidIconSize;

/**
 * Common interface for Font Awesome icons and brand icons.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
interface IFontAwesomeIcon
{
    public const SIZES = [16, 24, 32, 48, 64, 96, 128, 144];

    /**
     * Renders the HTML representation of an icon.
     *
     * @throws InvalidIconSize
     */
    public function toHtml(int $size = 16): string;
}
