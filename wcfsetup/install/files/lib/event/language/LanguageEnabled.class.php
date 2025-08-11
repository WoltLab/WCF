<?php

namespace wcf\event\language;

use wcf\data\language\Language;
use wcf\event\IPsr14Event;

/**
 * Indicates that a language has been enabled.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class LanguageEnabled implements IPsr14Event
{
    public function __construct(public readonly Language $language) {}
}
