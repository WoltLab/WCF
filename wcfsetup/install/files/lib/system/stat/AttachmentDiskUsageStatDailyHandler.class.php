<?php

namespace wcf\system\stat;

/**
 * Stat handler implementation for attachment disk usage.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class AttachmentDiskUsageStatDailyHandler extends AbstractFileDiskUsageStatDailyHandler
{
    #[\Override]
    public function getData(int $date)
    {
        return [
            'counter' => $this->getCounter($date, 'wcf1_attachment', 'uploadTime'),
            'total' => $this->getTotal($date, 'wcf1_attachment', 'uploadTime'),
        ];
    }
}
