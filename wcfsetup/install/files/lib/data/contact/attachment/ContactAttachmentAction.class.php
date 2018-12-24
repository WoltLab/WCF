<?php
namespace wcf\data\contact\attachment;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes actions on attachments of messages sent through the contact form.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Contact\Attachment
 * @since       3.2
 * 
 * @method ContactAttachmentEditor[] getObjects()
 * @method ContactAttachmentEditor getSingleObject()
 */
class ContactAttachmentAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ContactAttachmentEditor::class;
}
