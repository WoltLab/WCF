<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template modifier plugin which formats a unix timestamp.
 * Default date format contains year, month, day, hour and minute.
 *
 * Usage:
 *  {$timestamp|time}
 *  {"132845333"|time}
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 */
class TimeModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        $timestamp = \intval($tagArgs[0]);
        $dateTime = new \DateTimeImmutable('@' . $timestamp);

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
    }
}
