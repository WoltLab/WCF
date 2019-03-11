<?php
namespace wcf\data\contact\recipient;
use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents a contact recipient.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Recipient
 * @since	3.1
 * 
 * @property-read	integer		$recipientID		unique id of the recipient
 * @property-read	string		$name			name of the recipient
 * @property-read	string		$email			email address of the recipient
 * @property-read	integer		$showOrder		position of the recipient in relation to other recipients
 * @property-read	integer		$isAdministrator	is `1` if the recipient is the administrator and the `email` value equals `MAIL_ADMIN_ADDRESS`, otherwise `0`
 * @property-read	integer		$isDisabled		is `1` if the recipient is disabled and thus is not available for selection, otherwise `0`
 * @property-read	integer		$originIsSystem		is `1` if the recipient has been delivered by a package, otherwise `0` (i.e. the recipient has been created in the ACP)
 */
class ContactRecipient extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected function handleData($data) {
		// dynamically set email address for the administrator
		if (!empty($data['isAdministrator'])) {
			$data['email'] = MAIL_ADMIN_ADDRESS;
		}
		
		parent::handleData($data);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return WCF::getLanguage()->get($this->name);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __wakeup() {
		// update the administrator's email address on de-serialization, avoids outdated caches
		if (!empty($this->data['isAdministrator'])) {
			$this->data['isAdministrator'] = MAIL_ADMIN_ADDRESS;
		}
	}
}
