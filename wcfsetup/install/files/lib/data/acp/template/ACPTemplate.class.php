<?php

namespace wcf\data\acp\template;

use wcf\data\DatabaseObject;
use wcf\data\package\PackageCache;

/**
 * Represents an ACP template.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Acp\Template
 *
 * @property-read   int     $templateID unique id of the acp template
 * @property-read   int|null    $packageID  id of the package which delivers the acp template
 * @property-read   string      $templateName   name of the template
 * @property-read   string      $application    abbreviation of the application to which the template belongs
 */
class ACPTemplate extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'templateID';

    /**
     * Returns the path to this template.
     *
     * @return  string
     * @since   5.2
     */
    public function getPath()
    {
        return PackageCache::getInstance()->getPackage($this->packageID)->getAbsolutePackageDir() . 'acp/templates/' . $this->templateName . '.tpl';
    }

    /**
     * Returns the source of this template.
     *
     * @return  string
     * @since   5.2
     */
    public function getSource()
    {
        return @\file_get_contents($this->getPath());
    }
}
