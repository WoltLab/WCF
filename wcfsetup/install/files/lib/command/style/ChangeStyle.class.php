<?php

namespace wcf\command\style;

use wcf\data\style\Style;
use wcf\data\user\UserAction;
use wcf\event\style\StyleChanged;
use wcf\system\event\EventHandler;
use wcf\system\style\StyleHandler;
use wcf\system\WCF;

/**
 * Change the style for the current user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class ChangeStyle
{
    public function __construct(
        private readonly Style $style
    ) {}

    public function __invoke(): void
    {
        StyleHandler::getInstance()->changeStyle($this->style->styleID);
        if (StyleHandler::getInstance()->getStyle()->styleID !== $this->style->styleID) {
            // style could not be changed
            return;
        }

        if (WCF::getUser()->userID) {
            $this->saveUserStyle($this->style->styleID, (bool)$this->style->isDefault);
        } else {
            $this->saveGuestStyle($this->style->styleID, (bool)$this->style->isDefault);
        }

        $event = new StyleChanged($this->style);
        EventHandler::getInstance()->fire($event);
    }

    private function saveUserStyle(int $styleID, bool $isDefaultStyle): void
    {
        (new UserAction([WCF::getUser()], 'update', [
            'data' => [
                'styleID' => $isDefaultStyle ? 0 : $styleID,
            ],
        ]))->executeAction();
    }

    private function saveGuestStyle(int $styleID, bool $isDefaultStyle): void
    {
        if ($isDefaultStyle) {
            WCF::getSession()->unregister('styleID');
        } else {
            WCF::getSession()->register('styleID', $styleID);
        }
    }
}
