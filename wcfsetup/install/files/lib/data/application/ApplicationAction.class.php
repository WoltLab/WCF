<?php

namespace wcf\data\application;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\cache\builder\ApplicationCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\WCF;

/**
 * Executes application-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Application     create()
 * @method  ApplicationEditor[] getObjects()
 * @method  ApplicationEditor   getSingleObject()
 */
class ApplicationAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ApplicationEditor::class;

    /**
     * application editor object
     * @var ApplicationEditor
     */
    public $applicationEditor;

    /**
     * Assigns a list of applications to a group and computes cookie domain.
     */
    public function rebuild()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $sql = "UPDATE  wcf1_application
                SET     cookieDomain = ?
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);

        // calculate cookie domain
        $regex = new Regex(':[0-9]+');
        WCF::getDB()->beginTransaction();
        foreach ($this->getObjects() as $application) {
            $domainName = $application->domainName;
            if (\str_ends_with($regex->replace($domainName, ''), $application->cookieDomain)) {
                $domainName = $application->cookieDomain;
            }

            $statement->execute([
                $domainName,
                $application->packageID,
            ]);
        }
        WCF::getDB()->commitTransaction();

        // rebuild templates
        LanguageFactory::getInstance()->deleteLanguageCache();

        // reset application cache
        ApplicationCacheBuilder::getInstance()->reset();
    }

    /**
     * Marks an application as tainted, prevents loading it during uninstallation.
     */
    public function markAsTainted()
    {
        $applicationEditor = $this->getSingleObject();
        $applicationEditor->update(['isTainted' => 1]);

        ApplicationCacheBuilder::getInstance()->reset();
    }
}
