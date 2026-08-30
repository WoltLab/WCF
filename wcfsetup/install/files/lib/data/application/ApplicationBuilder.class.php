<?php

namespace wcf\data\application;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\package\Package;
use wcf\data\page\Page;

/**
 * Builder for creating, updating and deleting applications.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Application>
 */
final class ApplicationBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the package that delivers the application.
     */
    public function setPackage(Package $package): static
    {
        return $this->setID($package->packageID);
    }

    /**
     * Sets the domain that is used to access the application.
     *
     * The domain may not contain any path components, use `setDomainPath()` instead.
     */
    public function setDomainName(string $domainName): static
    {
        $this->properties['domainName'] = $domainName;

        return $this;
    }

    /**
     * Sets the path that is used to access the application.
     */
    public function setDomainPath(string $domainPath): static
    {
        $this->properties['domainPath'] = $domainPath;

        return $this;
    }

    /**
     * Sets the domain that is used to set cookies.
     *
     * The domain may not contain any path components.
     */
    public function setCookieDomain(string $cookieDomain): static
    {
        $this->properties['cookieDomain'] = $cookieDomain;

        return $this;
    }

    /**
     * Sets whether the application is being uninstalled and thus should not be
     * loaded during the uninstallation.
     */
    public function setIsTainted(bool $isTainted): static
    {
        $this->properties['isTainted'] = $isTainted ? 1 : 0;

        return $this;
    }

    /**
     * Sets the page that is used as the initial page when the application is
     * accessed without a controller name.
     */
    public function setLandingPage(?Page $landingPage): static
    {
        $this->properties['landingPageID'] = $landingPage?->pageID;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['packageID', 'domainName', 'domainPath', 'cookieDomain'];
    }
}
