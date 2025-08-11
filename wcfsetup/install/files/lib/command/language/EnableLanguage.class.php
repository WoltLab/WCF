<?php

namespace wcf\command\language;

use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\event\language\LanguageEnabled;
use wcf\system\event\EventHandler;

/**
 * Enables the given language.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class EnableLanguage
{
    public function __construct(private readonly Language $language) {}

    public function __invoke(): void
    {
        if ($this->language->isDefault) {
            return;
        }

        if (!$this->language->isDisabled) {
            return;
        }

        (new LanguageEditor($this->language))->update([
            'isDisabled' => 0,
        ]);

        $event = new LanguageEnabled($this->language);
        EventHandler::getInstance()->fire($event);
    }
}
