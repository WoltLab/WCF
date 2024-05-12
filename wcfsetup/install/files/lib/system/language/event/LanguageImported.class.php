<?php

namespace wcf\system\language\event;

use wcf\data\language\Language;
use wcf\system\event\IEvent;

/**
 * Indicates that a language was created or updated
 * through a manual import.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.1 use `wcf\event\language\LanguageImported` instead
 */
class LanguageImported implements IEvent
{
    public function __construct(
        public readonly Language $language
    ) {
    }
}
