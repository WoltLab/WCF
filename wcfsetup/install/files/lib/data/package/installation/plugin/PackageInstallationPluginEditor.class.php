<?php
namespace wcf\data\package\installation\plugin;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit package installation plugins.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.installation.plugin
 * @category	Community Framework
 * 
 * @method	PackageInstallationPlugin	getDecoratedObject()
 * @mixin	PackageInstallationPlugin
 */
class PackageInstallationPluginEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PackageInstallationPlugin::class;
}
