<?php

namespace wcf\event\language;

use wcf\data\language\Language;
use wcf\event\IPsr14Event;
use wcf\system\event\IEvent;

/**
 * Indicates that a language was created or updated through a manual import.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class LanguageImported extends \wcf\system\language\event\LanguageImported implements IPsr14Event
{
    public function __construct(
        public readonly Language $language
    ) {
    }
}
