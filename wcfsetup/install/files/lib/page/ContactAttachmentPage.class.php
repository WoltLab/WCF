<?php
namespace wcf\page;
use wcf\data\contact\attachment\ContactAttachment;
use wcf\system\exception\PermissionDeniedException;
use wcf\util\StringUtil;

/**
 * Shows an attachment.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Page
 */
class ContactAttachmentPage extends AttachmentPage {
	/**
	 * @var string
	 */
	public $accessKey = '';
	
	/**
	 * @var ContactAttachment
	 */
	public $contactAttachment;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'ContactAttachment';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['accessKey'])) $this->accessKey = StringUtil::trim($_GET['accessKey']);
		
		$this->contactAttachment = new ContactAttachment($this->attachment->attachmentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		AbstractPage::checkPermissions();
		
		if (!$this->attachment->canDownload()) {
			if (empty($this->accessKey) || !hash_equals($this->contactAttachment->accessKey, $this->accessKey)) {
				throw new PermissionDeniedException();
			}
		}
	}
}
