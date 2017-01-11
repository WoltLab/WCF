<?php
/**
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

// the content below is the effective result of `wcf\data\package\Package::writeConfigFile()`
// but the class is already in memory during the upgrade, causing the old version of this
// method to be used, which is not aware of `app.config.inc.php`

$content = <<<CONTENT
<?php
// com.woltlab.wcf (packageID 1)
if (!defined('WCF_DIR')) define('WCF_DIR', __DIR__.'/');
if (!defined('PACKAGE_ID')) define('PACKAGE_ID', 1);
if (!defined('PACKAGE_NAME')) define('PACKAGE_NAME', 'WoltLab Suite Core');
if (!defined('PACKAGE_VERSION')) define('PACKAGE_VERSION', '3.0.0');

CONTENT;

file_put_contents(WCF_DIR . 'app.config.inc.php', $content);
