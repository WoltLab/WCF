<?php

namespace wcf\system\template\plugin;

use wcf\system\template\TemplateEngine;
use wcf\util\DateUtil;

/**
 * Template modifier plugin which calculates the difference between two unix timestamps
 * and returns it as a textual date interval. The second parameter $fullInterval
 * indicates if the full difference is returned or just a rounded difference.
 *
 * Usage:
 *  {$endTimestamp|dateDiff}
 *  {$endTimestamp|dateDiff:$startTimestamp:$fullInterval}
 *
 * @author  Matthias Schmidt, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  since 3.1, use `DateIntervalFunctionTemplatePlugin`
 */
class DateDiffModifierTemplatePlugin implements IModifierTemplatePlugin
{
    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        if (!isset($tagArgs[1])) {
            $tagArgs[1] = TIME_NOW;
        }

        $fullInterval = false;
        if (isset($tagArgs[2])) {
            $fullInterval = $tagArgs[2];
        }

        $startTime = DateUtil::getDateTimeByTimestamp($tagArgs[1]);
        $endTime = DateUtil::getDateTimeByTimestamp($tagArgs[0]);

        return DateUtil::formatInterval($endTime->diff($startTime), $fullInterval);
    }
}
