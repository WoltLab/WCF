<?php

namespace wcf\system\stat;

/**
 * Stat handler implementation for attachment stats.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AttachmentStatDailyHandler extends AbstractStatDailyHandler
{
    /**
     * @inheritDoc
     */
    public function getData($date)
    {
        return [
            'counter' => $this->getCounter($date, 'wcf1_attachment', 'uploadTime'),
            'total' => $this->getTotal($date, 'wcf1_attachment', 'uploadTime'),
        ];
    }
}
