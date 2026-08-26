<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of attachments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template-covariant TDatabaseObject of Attachment|DatabaseObjectDecorator = Attachment
 * @extends DatabaseObjectList<TDatabaseObject>
 */
class AttachmentList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Attachment::class;
}
