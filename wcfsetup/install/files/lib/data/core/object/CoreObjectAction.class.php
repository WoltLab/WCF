<?php
namespace wcf\data\core\object;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes core object-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.core.object
 * @category	Community Framework
 * 
 * @method	CoreObject		create()
 * @method	CoreObjectEditor[]	getObjects()
 * @method	CoreObjectEditor	getSingleObject()
 */
class CoreObjectAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CoreObjectEditor::class;
}
