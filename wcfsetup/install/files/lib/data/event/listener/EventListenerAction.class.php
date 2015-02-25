<?php
namespace wcf\data\event\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes event listener-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.event.listener
 * @category	Community Framework
 */
class EventListenerAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\event\listener\EventListenerEditor';
}
