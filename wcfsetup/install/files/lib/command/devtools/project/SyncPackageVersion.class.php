<?php

namespace wcf\command\devtools\project;

use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\system\cache\builder\PackageCacheBuilder;

/**
 * Raises the version of an installed package to the version of its devtools
 * project. A version that is already equal or newer is left untouched.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class SyncPackageVersion
{
    public function __construct(
        private readonly Package $package,
        private readonly string $projectVersion,
    ) {}

    public function __invoke(): void
    {
        if (Package::compareVersion($this->package->packageVersion, $this->projectVersion, '>=')) {
            return;
        }

        new PackageEditor($this->package)->update([
            'packageVersion' => $this->projectVersion,
        ]);

        PackageCacheBuilder::getInstance()->reset();
    }
}
