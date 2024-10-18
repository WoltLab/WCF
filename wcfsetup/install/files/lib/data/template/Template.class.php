<?php

namespace wcf\data\template;

use wcf\data\DatabaseObject;
use wcf\data\package\PackageCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

/**
 * Represents a template.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $templateID     unique id of the template
 * @property-read   int $packageID      id of the package which delivers the template
 * @property-read   string $templateName       name of the template
 * @property-read   string $application        abbreviation of the application to which the template belongs
 * @property-read   int|null $templateGroupID    id of the template group to which the template belongs or `null` if the template belongs to no template group
 * @property-read   int $lastModificationTime   timestamp at which the template has been edited the last time
 */
class Template extends DatabaseObject
{
    /**
     * list of system critical templates
     * @var string[]
     */
    protected static $systemCriticalTemplates = ['headIncludeJavaScript', 'shared_wysiwyg', 'wysiwygToolbar'];

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * @inheritDoc
     */
    public function __construct($id, $row = null, ?DatabaseObject $object = null)
    {
        if ($id !== null) {
            $sql = "SELECT      template.*, template_group.templateGroupFolderName,
                                package.package
                    FROM        wcf1_template template
                    LEFT JOIN   wcf1_template_group template_group
                    ON          template_group.templateGroupID = template.templateGroupID
                    LEFT JOIN   wcf1_package package
                    ON          package.packageID = template.packageID
                    WHERE       template.templateID = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$id]);
            $row = $statement->fetchArray();

            if ($row === false) {
                $row = [];
            }
        } elseif ($object !== null) {
            $row = $object->data;
        }

        $this->handleData($row);
    }

    /**
     * Returns the path to this template.
     *
     * @return  string
     */
    public function getPath()
    {
        return $this->getPackageDir() . '/templates/' . $this->templateGroupFolderName . $this->templateName . '.tpl';
    }

    /**
     * @since 6.0
     */
    private function getPackageDir(): string
    {
        if ($this->application != 'wcf') {
            $application = ApplicationHandler::getInstance()->getApplication($this->application);
        } else {
            $application = ApplicationHandler::getInstance()->getWCF();
        }

        return \realpath(WCF_DIR . PackageCache::getInstance()->getPackage($application->packageID)->packageDir);
    }

    /**
     * Returns the source of this template.
     *
     * @return  string
     */
    public function getSource()
    {
        return @\file_get_contents($this->getPath());
    }

    /**
     * Returns true if current template is considered system critical and
     * may not be customized at any point.
     *
     * @return      bool
     */
    public function canCopy()
    {
        if (self::isSystemCritical($this->templateName)) {
            // system critical templates cannot be modified, because whatever the
            // gain of a customized version is, the damage potential is much higher
            return false;
        }

        return true;
    }

    /**
     * Returns true if current template is considered system critical and
     * may not be customized at any point.
     *
     * @param string $templateName
     * @return      bool
     */
    public static function isSystemCritical($templateName)
    {
        return \in_array($templateName, self::$systemCriticalTemplates);
    }
}
