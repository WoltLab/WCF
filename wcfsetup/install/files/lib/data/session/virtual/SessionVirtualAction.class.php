<?php
namespace wcf\data\session\virtual;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes virtual session-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.session.virtual
 * @category	Community Framework
 */
class SessionVirtualAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\session\virtual\SessionVirtualEditor';
	
	/**
	 * Attention: This method does not always return a new object, in case a matching virtual session
	 * already exists, the existing session will be returned rather than a new session being created.
	 * 
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		// try to find an existing virtual session
		$virtualSession = call_user_func(array($this->className, 'getExistingSession'), $this->parameters['sessionID']);
		if ($virtualSession !== null) {
			return $virtualSession;
		}
		
		if (!isset($this->parameters['lastActivityTime'])) $this->parameters['lastActivityTime'] = TIME_NOW;
		
		return parent::create();
	}
}
