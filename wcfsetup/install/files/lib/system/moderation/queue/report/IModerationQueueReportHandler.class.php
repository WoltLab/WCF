<?php

namespace wcf\system\moderation\queue\report;

use wcf\data\IUserContent;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\moderation\queue\IModerationQueueHandler;

/**
 * Default interface for moderation queue report handlers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IModerationQueueReportHandler extends IModerationQueueHandler
{
    /**
     * Returns true if current user can report given content.
     *
     * @return  bool
     */
    public function canReport(int $objectID);

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
     * @return  ?IUserContent
     */
    public function getReportedObject(int $objectID);
}
