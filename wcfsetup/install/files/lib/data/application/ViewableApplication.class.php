<?php

namespace wcf\data\application;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;

/**
 * Represents a viewable application.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `Application` instead.
 *
 * @mixin   Application
 * @extends DatabaseObjectDecorator<Application>
 */
class ViewableApplication extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Application::class;

    /**
     * package object
     * @var ?Package
     */
    protected $package;

    /**
     * Returns package object.
     *
     * @return  Package
     */
    public function getPackage()
    {
        if ($this->package === null) {
            $this->package = PackageCache::getInstance()->getPackage($this->packageID);
        }

        return $this->package;
    }
}
