<?php
namespace wcf\data\package\update;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package update-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category 	Community Framework
 */
class PackageUpdateAction extends AbstractDatabaseObjectAction {
	/**
	 * @see wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\update\PackageUpdateEditor';
}
