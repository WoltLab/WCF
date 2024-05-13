<?php

namespace wcf\event\language;

use wcf\data\language\Language;
use wcf\event\IPsr14Event;

/**
 * Indicates that a phrase has been modified by the user.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PhraseChanged extends \wcf\system\language\event\PhraseChanged implements IPsr14Event
{
    public function __construct(
        public readonly Language $language,
        public readonly string $name
    ) {
    }
}
