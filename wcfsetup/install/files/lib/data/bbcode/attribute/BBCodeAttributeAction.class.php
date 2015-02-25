<?php
namespace wcf\data\bbcode\attribute;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes bbcode attribute-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.bbcode.attribute
 * @category	Community Framework
 */
class BBCodeAttributeAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\bbcode\attribute\BBCodeAttributeEditor';
}
