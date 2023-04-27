<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

/**
 * Template modifier plugin which renders a \DateTimeInterface or
 * a unix timestamp into a date and time format.
 *
 * Usage:
 *  {$timestamp|plainTime}
 *  {"132845333"|plainTime}
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.0 use `{time type='plainTime'}` instead
 */
class PlainTimeModifierTemplatePlugin implements IModifierTemplatePlugin
{
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

        return \IntlDateFormatter::create(
            WCF::getLanguage()->getLocale(),
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::SHORT,
            WCF::getUser()->getTimeZone()
        )->format($dateTime);
    }
}
