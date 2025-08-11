<?php

namespace wcf\command\contact\option;

use wcf\data\contact\option\ContactOption;
use wcf\data\contact\option\ContactOptionEditor;
use wcf\event\contact\option\ContactOptionEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables the contact option.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableContactOption
{
    public function __construct(
        private readonly ContactOption $contactOption
    ) {}

    public function __invoke(): void
    {
        if (!$this->contactOption->isDisabled) {
            return;
        }

        (new ContactOptionEditor($this->contactOption))->update([
            'isDisabled' => 0,
        ]);

        $event = new ContactOptionEnabled($this->contactOption);
        EventHandler::getInstance()->fire($event);
    }
}
