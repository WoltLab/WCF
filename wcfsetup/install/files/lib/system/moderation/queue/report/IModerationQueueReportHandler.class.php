<?php

namespace wcf\system\moderation\queue\report;

use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\IModerationQueueHandler;

/**
 * Default interface for moderation queue report handlers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Moderation\Queue\Report
 */
interface IModerationQueueReportHandler extends IModerationQueueHandler
{
    /**
     * Returns true if current user can report given content.
     *
     * @param int $objectID
     * @return  bool
     */
    public function canReport($objectID);

    /**
     * Returns rendered template for reported content.
     *
     * @param ViewableModerationQueue $queue
     * @return  string
     */
    public function getReportedContent(ViewableModerationQueue $queue);

    /**
     * Returns reported object.
     *
     * @param int $objectID
     * @return  \wcf\data\IUserContent
     */
    public function getReportedObject($objectID);
}
