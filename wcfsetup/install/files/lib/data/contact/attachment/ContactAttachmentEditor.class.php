<?php
namespace wcf\data\contact\attachment;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit attachments attached to messages sent through the contact form.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Contact\Attachment
 * @since       5.2
 * 
 * @method static ContactAttachment create(array $parameters = [])
 * @method ContactAttachment getDecoratedObject()
 * @mixin ContactAttachment
 */
class ContactAttachmentEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ContactAttachment::class;
}
