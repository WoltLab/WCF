<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package installation plugin-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.plugin
 * @category	Community Framework
 */
class PackageInstallationPluginAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\package\installation\plugin\PackageInstallationPluginEditor';
}
