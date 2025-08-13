<?php

namespace wcf\command\language;

use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\event\language\LanguageDisabled;
use wcf\system\event\EventHandler;

/**
 * Disables a language.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class DisableLanguage
{
    public function __construct(private readonly Language $language) {}

    public function __invoke(): void
    {
        (new LanguageEditor($this->language))->update([
            'isDisabled' => 1,
        ]);

        $event = new LanguageDisabled($this->language);
        EventHandler::getInstance()->fire($event);
    }
}
