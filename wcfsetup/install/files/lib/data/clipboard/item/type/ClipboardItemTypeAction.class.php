<?php
namespace wcf\data\clipboard\item\type;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes clipboard item type-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.item.type
 * @category 	Community Framework
 */
class ClipboardItemTypeAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\clipboard\item\type\ClipboardItemTypeEditor';
}
