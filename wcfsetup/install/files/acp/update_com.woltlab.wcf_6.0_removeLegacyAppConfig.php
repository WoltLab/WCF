<?php

/**
 * Removes the legacy config.inc.php from application directories.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\data\application\ApplicationList;
use wcf\util\FileUtil;

$list = new ApplicationList();
$list->readObjects();

foreach ($list as $application) {
    $package = $application->getPackage();

    if ($package->package === 'com.woltlab.wcf') {
        continue;
    }

    $legacyConfig = FileUtil::addTrailingSlash(WCF_DIR . $package->packageDir) . 'config.inc.php';

    if (\file_exists($legacyConfig) && \md5_file($legacyConfig) === 'e13ffdb5262e68a066d2486e468df685') {
        \unlink($legacyConfig);
    }
}
