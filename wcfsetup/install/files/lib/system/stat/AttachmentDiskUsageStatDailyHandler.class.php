<?php

namespace wcf\system\stat;

/**
 * Stat handler implementation for attachment disk usage.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AttachmentDiskUsageStatDailyHandler extends AbstractDiskUsageStatDailyHandler
{
    /**
     * @inheritDoc
     */
    public function getData($date)
    {
        return [
            'counter' => $this->getCounter($date, 'wcf' . WCF_N . '_attachment', 'uploadTime'),
            'total' => $this->getTotal($date, 'wcf' . WCF_N . '_attachment', 'uploadTime'),
        ];
    }
}
