<?php
namespace wcf\data\session;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes session-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session
 * @category	Community Framework
 */
class SessionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\session\SessionEditor';
}
