<?php

namespace wcf\system\style\command;

use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\system\cache\builder\StyleCacheBuilder;
use wcf\system\style\StyleHandler;

/**
 * Adds the dark color scheme to a style.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class AddDarkMode
{
    private readonly int $styleID;

    private readonly StyleHandler $styleHandler;

    public function __construct(Style $style)
    {
        $this->styleID = $style->styleID;

        $this->styleHandler = StyleHandler::getInstance();
    }

    public function __invoke(): void
    {
        $styleEditor = new StyleEditor(new Style($this->styleID));
        $styleEditor->update([
            'hasDarkMode' => 1,
        ]);

        $style = new Style($this->styleID);

        StyleCacheBuilder::getInstance()->reset();
        $this->styleHandler->resetStylesheet($style);
    }
}
