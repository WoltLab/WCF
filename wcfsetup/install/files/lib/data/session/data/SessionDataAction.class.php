<?php
namespace wcf\data\session\data;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes session data-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2010 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.data
 * @category 	Community Framework
 */
class SessionDataAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\session\data\SessionDataEditor';
}
