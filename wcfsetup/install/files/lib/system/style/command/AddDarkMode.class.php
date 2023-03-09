<?php

namespace wcf\system\style\command;

use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\system\cache\builder\StyleCacheBuilder;

/**
 * Adds the dark color scheme to a style.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Style\Command
 * @since 6.0
 */
final class AddDarkMode
{
    private readonly int $styleID;

    public function __construct(Style $style)
    {
        $this->styleID = $style->styleID;
    }

    public function __invoke(): void
    {
        $styleEditor = new StyleEditor(new Style($this->styleID));
        $styleEditor->update([
            'hasDarkMode' => 1,
        ]);

        StyleCacheBuilder::getInstance()->reset();
    }
}
