<?php
namespace wcf\data\acp\session\data;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP session data-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.data
 * @category 	Community Framework
 */
class ACPSessionDataAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\acp\session\data\ACPSessionDataEditor';
}
