<?php

namespace wcf\system\package;

use wcf\system\setup\IFileHandler;

/**
 * Abstract file handler implementation for all file installations during the package
 * installation.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class PackageInstallationFileHandler implements IFileHandler
{
    /**
     * abbreviation of the application the files belong to
     * @var string
     */
    protected $application = '';

    /**
     * active package installation dispatcher
     * @var PackageInstallationDispatcher
     */
    protected $packageInstallation;

    public function __construct(PackageInstallationDispatcher $packageInstallation, string $application)
    {
        $this->packageInstallation = $packageInstallation;
        $this->application = $application;
    }
}
