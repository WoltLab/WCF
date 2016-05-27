<?php
namespace wcf\data\event\listener;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit event listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.event.listener
 * @category	Community Framework
 * 
 * @method	EventListener	getDecoratedObject()
 * @mixin	EventListener
 */
class EventListenerEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = EventListener::class;
}
