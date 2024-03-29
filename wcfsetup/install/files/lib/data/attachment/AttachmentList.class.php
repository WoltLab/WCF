<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Attachment      current()
 * @method  Attachment[]        getObjects()
 * @method  Attachment|null     getSingleObject()
 * @method  Attachment|null     search($objectID)
 * @property    Attachment[] $objects
 */
class AttachmentList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Attachment::class;
}
