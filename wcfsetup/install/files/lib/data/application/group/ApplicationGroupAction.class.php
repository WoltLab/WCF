<?php
namespace wcf\data\application\group;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes application group-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application.group
 * @category 	Community Framework
 */
class ApplicationGroupAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\application\group\ApplicationGroupEditor';
}
