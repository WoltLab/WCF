<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template modifier plugin which renders a \DateTimeInterface or
 * a unix timestamp as `<woltlab-core-date-time>`.
 *
 * Usage:
 *  {$foo->getDateTime()|time}
 *  {$bar->time|time}
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.0 use `{time}` instead
 */
class TimeModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /** @var array<string, \IntlDateFormatter> */
    private array $dateFormatter = [];

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        if ($tagArgs[0] instanceof \DateTimeInterface) {
            $dateTime = $tagArgs[0];
        } else {
            $timestamp = \intval($tagArgs[0]);
            $dateTime = new \DateTimeImmutable('@' . $timestamp);
        }

        $isFutureDate = $dateTime->getTimestamp() > TIME_NOW;

        $locale = WCF::getLanguage()->getLocale();
        $timeZone = WCF::getUser()->getTimeZone();

        $key = $locale . '::' . $timeZone->getName();
        $dateFormatter = $this->dateFormatter[$key] ?? null;
        if ($dateFormatter === null) {
            $dateFormatter = \IntlDateFormatter::create(
                $locale,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::SHORT,
                $timeZone
            );

            $this->dateFormatter[$key] = $dateFormatter;
        }

        $dateAndTime = $dateFormatter->format($dateTime);

        return \sprintf(
            '<woltlab-core-date-time date="%s"%s>%s</woltlab-core-date-time>',
            $dateTime->format('c'),
            $isFutureDate ? ' static' : '',
            $dateAndTime
        );
    }
}
