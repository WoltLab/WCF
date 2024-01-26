<?php

namespace wcf\system\template;

use wcf\system\WCF;

/**
 * Loads and displays shared templates for any environment.
 *
 * @author  Olaf Braun
 * @copyright   2001-2014 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class SharedTemplateEngine extends TemplateEngine
{
    /**
     * @inheritDoc
     */
    protected $environment = 'shared';

    /**
     * @inheritDoc
     */
    public function getTemplateGroupID()
    {
        static $initialized = false;

        if (!$initialized) {
            $sql = "SELECT  templateGroupID
                    FROM    wcf" . WCF_N . "_template_group
                    WHERE   templateGroupFolderName = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute(['_wcf_shared/']);

            parent::setTemplateGroupID($statement->fetchSingleColumn());
            $initialized = true;
        }

        return parent::getTemplateGroupID();
    }

    /**
     * This method always throws, because changing the template group is not supported.
     *
     * @param int $templateGroupID
     * @throws  \BadMethodCallException
     */
    public function setTemplateGroupID($templateGroupID)
    {
        throw new \BadMethodCallException("You may not change the template group of the shared template engine");
    }
}
