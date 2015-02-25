<?php
namespace wcf\data\style\variable;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes style variable-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.style.variable
 * @category	Community Framework
 */
class StyleVariableAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\style\variable\StyleVariableEditor';
}
