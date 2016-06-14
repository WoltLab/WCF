<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package installation plugin-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Plugin
 * 
 * @method	PackageInstallationPlugin		create()
 * @method	PackageInstallationPluginEditor[]	getObjects()
 * @method	PackageInstallationPluginEditor		getSingleObject()
 */
class PackageInstallationPluginAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PackageInstallationPluginEditor::class;
}
