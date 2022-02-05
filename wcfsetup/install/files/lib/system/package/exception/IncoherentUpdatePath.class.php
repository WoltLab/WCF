<?php

namespace wcf\system\package\exception;

use wcf\data\package\PackageCache;
use wcf\system\WCF;

/**
 * Caused by gaps in the update path where a never version is requested but
 * there are no updates in-between that would allow a step-by-step update
 * to the requested version.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Exception
 * @since 5.5
 */
final class IncoherentUpdatePath extends \Exception
{
    public function __construct(string $package, string $currentVersion, string $newVersion)
    {
        parent::__construct(
            WCF::getLanguage()->getDynamicVariable(
                'wcf.acp.package.update.path.incoherent',
                [
                    'currentVersion' => $currentVersion,
                    'newVersion' => $newVersion,
                    'package' => $package,
                    'packageName' => PackageCache::getInstance()->getPackageByIdentifier($package)->getName(),
                ]
            )
        );
    }
}
