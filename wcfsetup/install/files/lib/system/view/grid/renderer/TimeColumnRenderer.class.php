<?php

namespace wcf\system\view\grid\renderer;

use wcf\system\WCF;

class TimeColumnRenderer extends AbstractColumnRenderer
{
    public function render(mixed $value, mixed $context = null): string
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

    public function getClasses(): string
    {
        return 'columnDate';
    }
}
