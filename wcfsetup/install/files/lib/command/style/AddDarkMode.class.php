<?php

namespace wcf\command\style;

use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\style\StyleHandler;

/**
 * Adds the dark color scheme to a style.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class AddDarkMode
{
    public function __construct(
        private readonly Style $style
    ) {}

    public function __invoke(): void
    {
        $styleEditor = new StyleEditor($this->style);
        $styleEditor->update([
            'hasDarkMode' => 1,
        ]);

        $style = new Style($this->style->styleID);

        StyleCacheBuilder::getInstance()->reset();
        StyleHandler::getInstance()->resetStylesheet($style);
    }
}
