<?php

namespace wcf\command\box;

use wcf\data\box\Box;
use wcf\data\box\BoxEditor;
use wcf\event\box\BoxEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables a box.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableBox
{
    public function __construct(private readonly Box $box) {}

    public function __invoke(): void
    {
        if (!$this->box->isDisabled) {
            return;
        }

        (new BoxEditor($this->box))->update([
            'isDisabled' => 0,
        ]);

        $event = new BoxEnabled($this->box);
        EventHandler::getInstance()->fire($event);
    }
}
