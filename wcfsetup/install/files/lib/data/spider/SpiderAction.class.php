<?php
namespace wcf\data\spider;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes spider-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.spider
 * @category	Community Framework
 */
class SpiderAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\spider\SpiderEditor';
}
