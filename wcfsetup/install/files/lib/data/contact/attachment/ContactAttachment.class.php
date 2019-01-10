<?php
namespace wcf\data\contact\attachment;
use wcf\data\attachment\Attachment;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\system\request\LinkHandler;
use wcf\util\StringUtil;

/**
 * Represents a contact attachment.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Contact\Attachment
 * @since       5.2
 * 
 * @property-read int $attachmentID
 * @property-read string $accessKey
 */
class ContactAttachment extends DatabaseObject implements ITitledLinkObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'attachmentID';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexIsIdentity = false;
	
	/**
	 * @var Attachment
	 */
	protected $attachment;
	
	/**
	 * @return Attachment
	 */
	public function getAttachment() {
		if ($this->attachment === null) {
			$this->attachment = new Attachment($this->attachmentID);
		}
		
		return $this->attachment;
	}
	
	/**
	 * @return string
	 */
	public static function generateKey() {
		return StringUtil::getRandomID();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getAttachment()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('ContactAttachment', [
			'object' => $this->getAttachment(),
			'accessKey' => $this->accessKey,
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
}
