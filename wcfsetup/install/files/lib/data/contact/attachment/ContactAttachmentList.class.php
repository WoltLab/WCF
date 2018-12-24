<?php
namespace wcf\data\contact\attachment;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of attachments of messages sent through the contact form.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Contact\Attachment
 * @since       3.2
 * 
 * @method ContactAttachment current()
 * @method ContactAttachment[] getObjects()
 * @method ContactAttachment|null search($objectID)
 * @property ContactAttachment[] $objects
 */
class ContactAttachmentList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ContactAttachment::class;
}
