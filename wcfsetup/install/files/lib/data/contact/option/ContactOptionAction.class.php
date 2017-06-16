<?php
namespace wcf\data\contact\recipient;
use wcf\data\custom\option\CustomOptionAction;

/**
 * Executes contact option related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Option
 * @since	3.1
 * 
 * @method	ContactOptionEditor[]	getObjects()
 * @method	ContactOptionEditor	getSingleObject()
 */
class ContactOptionAction extends CustomOptionAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ContactOptionEditor::class;
	
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
}
