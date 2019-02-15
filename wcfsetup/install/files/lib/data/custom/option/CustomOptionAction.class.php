<?php
namespace wcf\data\custom\option;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\TDatabaseObjectToggle;

/**
 * Executes option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Custom\Option
 * @since	3.1
 * 
 * @method	CustomOption		create()
 * @method	CustomOptionEditor[]	getObjects()
 * @method	CustomOptionEditor	getSingleObject()
 */
abstract class CustomOptionAction extends AbstractDatabaseObjectAction implements IToggleAction {
	use TDatabaseObjectToggle;
	
	/**
	 * @inheritDoc
	 */
	protected $className = CustomOptionEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
}
