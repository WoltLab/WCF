<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of template listener.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.listener
 * @category 	Community Framework
 */
class TemplateListenerList extends DatabaseObjectList {
	/**
	 * @see	DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\template\listener\TemplateListener';
}
