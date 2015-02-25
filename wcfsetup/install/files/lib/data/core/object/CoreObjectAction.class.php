<?php
namespace wcf\data\core\object;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes core object-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.core.object
 * @category	Community Framework
 */
class CoreObjectAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\core\object\CoreObjectEditor';
}
