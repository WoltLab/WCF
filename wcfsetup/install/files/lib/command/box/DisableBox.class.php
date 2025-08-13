<?php

namespace wcf\command\box;

use wcf\data\box\Box;
use wcf\data\box\BoxEditor;
use wcf\event\box\BoxDisabled;
use wcf\system\event\EventHandler;

/**
 * Enables a box.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableBox
{
    public function __construct(private readonly Box $box) {}

    public function __invoke(): void
    {
        (new BoxEditor($this->box))->update([
            'isDisabled' => 1,
        ]);

        $event = new BoxDisabled($this->box);
        EventHandler::getInstance()->fire($event);
    }
}
