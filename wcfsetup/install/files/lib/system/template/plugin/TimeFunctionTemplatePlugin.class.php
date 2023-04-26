<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Template function plugin which renders a \DateTimeInterface or
 * a unix timestamp into a human readable format.
 *
 * Usage:
 *  {time time=$timestamp}
 *  {time time=$timestamp type='plain'}
 *  {time time=$timestamp type='date' format='Y-d-m'}
 *
 * @author Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class TimeFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    public function execute($tagArgs, TemplateEngine $tplObj): string
    {
        $time = $tagArgs['time'] ?? '';
        $type = $tagArgs['type'] ?? '';

        if ($time instanceof \DateTimeInterface) {
            $dateTime = $time;
        } else {
            $timestamp = \intval($time);
            $dateTime = new \DateTimeImmutable('@' . $timestamp);
        }

        if ($type === '') {
            $isFutureDate = $dateTime->getTimestamp() > TIME_NOW;

            $dateAndTime = \IntlDateFormatter::create(
                WCF::getLanguage()->getLocale(),
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::SHORT,
                WCF::getUser()->getTimeZone()
            )->format($dateTime);

            return \sprintf(
                '<woltlab-core-date-time date="%s"%s>%s</woltlab-core-date-time>',
                $dateTime->format('c'),
                $isFutureDate ? ' static' : '',
                $dateAndTime
            );
        } else if ($type === 'plain') {
            return \str_replace(
                '%time%',
                DateUtil::format($dateTime, DateUtil::TIME_FORMAT),
                \str_replace(
                    '%date%',
                    DateUtil::format($dateTime, DateUtil::DATE_FORMAT),
                    WCF::getLanguage()->get('wcf.date.dateTimeFormat')
                )
            );
        } else if ($type === 'date') {
            $format = $tagArgs['format'] ?? '';
            return DateUtil::format(
                $dateTime,
                $format ?: DateUtil::DATE_FORMAT
            );
        } else {
            throw new \InvalidArgumentException("Invalid type '{$type}' given.");
        }
    }
}
