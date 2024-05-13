<?php

namespace wcf\system\language\event;

use wcf\data\language\Language;
use wcf\system\event\IEvent;

/**
 * Indicates that the contents of a language should be copied to another one.
 *
 * @author Florian Gail
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.1 use `wcf\event\language\LanguageContentCopying` instead
 */
class LanguageContentCopying implements IEvent
{
    public function __construct(
        public readonly Language $sourceLanguage,
        public readonly Language $targetLanguage,
    ) {
    }
}
