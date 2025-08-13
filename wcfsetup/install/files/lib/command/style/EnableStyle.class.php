<?php

namespace wcf\command\style;

use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\event\style\StyleEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a style.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableStyle
{
    public function __construct(private readonly Style $style) {}

    public function __invoke(): void
    {
        (new StyleEditor($this->style))->update([
            'isDisabled' => 0,
        ]);

        $event = new StyleEnabled($this->style);
        EventHandler::getInstance()->fire($event);
    }
}
