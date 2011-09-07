<?php
namespace wcf\data\clipboard\item\type;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit clipboard item types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.item.type
 * @category 	Community Framework
 */
class ClipboardItemTypeEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\clipboard\item\type\ClipboardItemType';
}
