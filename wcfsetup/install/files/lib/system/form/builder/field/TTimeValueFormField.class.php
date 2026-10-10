<?php

namespace wcf\system\form\builder\field;

use wcf\system\WCF;

/**
 * Provides methods for form fields whose values are times of day in `H:i` format.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TTimeValueFormField
{
    /**
     * Returns the given time in `H:i` format or `null` if it is neither given
     * in `H:i` nor in `H:i:s` format. Seconds are discarded.
     */
    protected function normalizeTime(string $time): ?string
    {
        if (!\preg_match('~^(?<hour>[01]\d|2[0-3]):(?<minute>[0-5]\d)(?::[0-5]\d)?\z~', $time, $matches)) {
            return null;
        }

        return $matches['hour'] . ':' . $matches['minute'];
    }

    /**
     * Returns the given time in `H:i` format formatted in the user's locale.
     */
    protected function formatTime(string $time): string
    {
        $dateTime = \DateTimeImmutable::createFromFormat(
            '!H:i',
            $time,
            new \DateTimeZone('UTC')
        );

        $formatter = new \IntlDateFormatter(
            WCF::getLanguage()->getLocale(),
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            new \DateTimeZone('UTC')
        );

        return $formatter->format($dateTime);
    }
}
