<?php
namespace wcf\data\contact\recipient;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes contact recipient related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Recipient
 * @since	3.1
 * 
 * @method	ContactRecipient[]	getObjects()
 * @method	ContactRecipient	getSingleObject()
 */
class ContactRecipientAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ContactRecipientEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.contact.canManageContactForm'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.contact.canManageContactForm'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.contact.canManageContactForm'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		parent::validateDelete();
		
		foreach ($this->getObjects() as $object) {
			if ($object->originIsSystem) {
				throw new PermissionDeniedException();
			}
		}
	}
}
