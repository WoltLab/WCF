<?php

namespace wcf\command\style;

use wcf\data\style\Style;
use wcf\data\style\StyleEditor;
use wcf\event\style\StyleDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables the given style.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableStyle
{
    public function __construct(private readonly Style $style) {}

    public function __invoke(): void
    {
        (new StyleEditor($this->style))->update([
            'isDisabled' => 1,
        ]);

        $event = new StyleDisabled($this->style);
        EventHandler::getInstance()->fire($event);
    }
}
