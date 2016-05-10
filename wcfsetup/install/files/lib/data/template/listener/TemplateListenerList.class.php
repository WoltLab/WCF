<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of template listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.listener
 * @category	Community Framework
 *
 * @method	TemplateListener		current()
 * @method	TemplateListener[]		getObjects()
 * @method	TemplateListener|null		search($objectID)
 * @property	TemplateListener[]		$objects
 */
class TemplateListenerList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = TemplateListener::class;
}
