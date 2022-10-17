<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

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
        $dateTimeObject = DateUtil::getDateTimeByTimestamp($timestamp);
        $isFutureDate = ($timestamp > TIME_NOW);

        return \sprintf(
            '<woltlab-core-time date="%s"%s></woltlab-core-time>',
            DateUtil::format($dateTimeObject, 'c'),
            $isFutureDate ? ' static' : '',
        );
    }
}
