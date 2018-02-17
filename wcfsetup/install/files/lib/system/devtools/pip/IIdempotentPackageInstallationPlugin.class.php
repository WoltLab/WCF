<?php
namespace wcf\system\devtools\pip;
use wcf\system\package\plugin\IPackageInstallationPlugin;

/**
 * Default interface for package installation plugins that indicate to have
 * no side-effects when run more than once. Furthermore such plugins may not
 * throw a `SplitNodeException` during its normal operation.
 * 
 * This is especially important for the developer tools that rely on PIPs
 * being invokable at any point, regardless if they have been ran before.
 * 
 * Classic examples include the menu-PIPs that will at most update existing
 * data, but not cause items to be duplicated when evaluated once more. The
 * opposite is the sql-PIP that (usually) contains non-repeatable instructions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since       3.1
 */
interface IIdempotentPackageInstallationPlugin extends IPackageInstallationPlugin {
	/**
	 * Returns a list of package installation plugins that need to be
	 * executed prior to a call to this PIP.
	 * 
	 * This method is only considered for the bulk sync in the developer
	 * tools and has no impact on the regular installation process.
	 * 
	 * @return      string[]
	 */
	public static function getSyncDependencies();
}
