<?php
namespace wcf\data\event\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes event listener-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.event.listener
 * @category	Community Framework
 * 
 * @method	EventListener		create()
 * @method	EventListenerEditor[]	getObjects()
 * @method	EventListenerEditor	getSingleObject()
 */
class EventListenerAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = EventListenerEditor::class;
}
