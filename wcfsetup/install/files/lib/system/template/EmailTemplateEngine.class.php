<?php

namespace wcf\system\template;

use wcf\system\WCF;

/**
 * Loads and displays templates in emails.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class EmailTemplateEngine extends TemplateEngine
{
    /**
     * @inheritDoc
     */
    protected $environment = 'email';

    /**
     * @inheritDoc
     */
    public function getTemplateGroupID()
    {
        static $initialized = false;

        if (!$initialized) {
            $sql = "SELECT  templateGroupID
                    FROM    wcf1_template_group
                    WHERE   templateGroupFolderName = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute(['_wcf_email/']);

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
        throw new \BadMethodCallException("You may not change the template group of the email template engine");
    }
}
