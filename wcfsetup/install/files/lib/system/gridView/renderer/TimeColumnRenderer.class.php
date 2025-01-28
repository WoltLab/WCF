<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Renders a unix timestamp into a human readable format.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class TimeColumnRenderer extends AbstractColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        $timestamp = \intval($value);
        if (!$timestamp) {
            return '';
        }

        $dateTime = new \DateTimeImmutable('@' . $timestamp);
        $dateTime = $dateTime->setTimezone(WCF::getUser()->getTimeZone());
        $locale = WCF::getLanguage()->getLocale();

        $isFutureDate = $dateTime->getTimestamp() > TIME_NOW;

        $dateAndTime = \IntlDateFormatter::formatObject(
            $dateTime,
            [
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::SHORT,
            ],
            $locale
        );

        return \sprintf(
            '<woltlab-core-date-time date="%s"%s>%s</woltlab-core-date-time>',
            $dateTime->format('c'),
            $isFutureDate ? ' static' : '',
            $dateAndTime
        );
    }

    #[\Override]
    public function getClasses(): string
    {
        return 'gridView__column--date';
    }
}
