<?php

namespace wcf\event\language;

use wcf\data\language\Language;
use wcf\event\IPsr14Event;

/**
 * Indicates that the contents of a language should be copied to another one.
 *
 * @author      Florian Gail
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class LanguageContentCopying extends \wcf\system\language\event\LanguageContentCopying implements IPsr14Event
{
    public function __construct(
        public readonly Language $sourceLanguage,
        public readonly Language $targetLanguage,
    ) {
    }
}
