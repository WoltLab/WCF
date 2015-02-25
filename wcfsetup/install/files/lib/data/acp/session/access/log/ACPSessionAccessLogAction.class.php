<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session access log-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category	Community Framework
 */
class ACPSessionAccessLogAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\acp\session\access\log\ACPSessionAccessLogEditor';
}
