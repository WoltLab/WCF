<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template function plugin which renders a \DateTimeInterface or
 * a unix timestamp into a human readable format.
 *
 * The timezone will be set to the current user's timezone for all
 * output types.
 *
 * Usage:
 *  {time time=$timestamp}
 *  {time time=$timestamp type='plainTime'}
 *  {time time=$timestamp type='plainDate'}
 *  {time time=$timestamp type='machine'}
 *  {time time=$timestamp type='custom' format='Y-m-d'}
 *
 * @author Tim Duesterhus, Marcel Werk
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class TimeFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    #[\Override]
    public function execute(array $tagArgs, TemplateEngine $tplObj): string
    {
        if (!isset($tagArgs['time'])) {
            throw new \InvalidArgumentException("Missing parameter 'time'.");
        }

        $time = $tagArgs['time'];
        $type = $tagArgs['type'] ?? 'interactive';

        if ($time instanceof \DateTimeImmutable) {
            $dateTime = $time;
        } elseif ($time instanceof \DateTime) {
            // Ensure we do not modify the original object.
            $dateTime = \DateTimeImmutable::createFromMutable($time);
        } elseif (\is_string($time) || \is_int($time)) {
            $timestamp = \intval($time);
            $dateTime = (new \DateTimeImmutable('@' . $timestamp));
        } else {
            throw new \InvalidArgumentException("Unknown data type for 'time' given.");
        }

        $timeZone = WCF::getUser()->getTimeZone();
        $dateTime = $dateTime->setTimezone($timeZone);
        $locale = WCF::getLanguage()->getLocale();

        switch ($type) {
            case 'interactive':
                $isFutureDate = $dateTime->getTimestamp() > \TIME_NOW;

                $dateAndTime = self::getFormatter(
                    $locale,
                    $timeZone,
                    \IntlDateFormatter::LONG,
                    \IntlDateFormatter::SHORT
                )->format($dateTime);

                return \sprintf(
                    '<woltlab-core-date-time date="%s"%s>%s</woltlab-core-date-time>',
                    $dateTime->format('c'),
                    $isFutureDate ? ' static' : '',
                    $dateAndTime
                );
            case 'plainTime':
                return self::getFormatter(
                    $locale,
                    $timeZone,
                    \IntlDateFormatter::LONG,
                    \IntlDateFormatter::SHORT
                )->format($dateTime);
            case 'plainDate':
                return self::getFormatter(
                    $locale,
                    $timeZone,
                    \IntlDateFormatter::LONG,
                    \IntlDateFormatter::NONE
                )->format($dateTime);
            case 'machine':
                return $dateTime->format(\DateTimeInterface::ATOM);
            case 'custom':
                return $dateTime->format($tagArgs['format']);
            default:
                throw new \InvalidArgumentException("Invalid type '{$type}' given.");
        }
    }

    /**
     * Constructing an `IntlDateFormatter` costs about ten times as much as formatting
     * with it, and `IntlDateFormatter::formatObject()` constructs one on every call.
     */
    private static function getFormatter(
        string $locale,
        \DateTimeZone $timeZone,
        int $dateType,
        int $timeType
    ): \IntlDateFormatter {
        static $formatters = [];

        $key = "{$locale}|{$timeZone->getName()}|{$dateType}|{$timeType}";
        if (!isset($formatters[$key])) {
            $formatters[$key] = new \IntlDateFormatter($locale, $dateType, $timeType, $timeZone);
        }

        return $formatters[$key];
    }
}
