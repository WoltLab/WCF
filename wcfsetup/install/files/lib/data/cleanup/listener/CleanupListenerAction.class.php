<?php
namespace wcf\data\cleanup\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes cleanup listener-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cleanup.listener
 * @category 	Community Framework
 */
class CleanupListenerAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\cleanup\listener\CleanupListenerEditor';
}
