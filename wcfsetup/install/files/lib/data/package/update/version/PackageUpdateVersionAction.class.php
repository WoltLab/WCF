<?php
namespace wcf\data\package\update\version;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package update version-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.version
 * @category 	Community Framework
 */
class PackageUpdateVersionAction extends AbstractDatabaseObjectAction {
	/**
	 * @see AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\update\version\PackageUpdateVersionEditor';
}
