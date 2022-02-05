<?php

namespace wcf\system\package\exception;

use wcf\data\package\PackageCache;
use wcf\system\WCF;

/**
 * Triggered when the requested package version does not exist or is unvailable.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Exception
 * @since 5.5
 */
final class UnknownUpdatePath extends \Exception
{
    public function __construct(string $package, string $currentVersion, string $newVersion)
    {
        parent::__construct(
            WCF::getLanguage()->getDynamicVariable('wcf.acp.package.update.path.unknown', [
                'currentVersion' => $currentVersion,
                'newVersion' => $newVersion,
                'package' => $package,
                'packageName' => PackageCache::getInstance()->getPackageByIdentifier($package)->getName(),
            ])
        );
    }
}
