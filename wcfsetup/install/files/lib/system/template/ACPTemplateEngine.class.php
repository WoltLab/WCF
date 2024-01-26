<?php

namespace wcf\system\template;

use wcf\system\application\ApplicationHandler;

/**
 * Loads and displays template in the ACP.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ACPTemplateEngine extends TemplateEngine
{
    /**
     * @inheritDoc
     */
    protected $environment = 'admin';

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $this->templatePaths = ['wcf' => WCF_DIR . 'acp/templates/'];
        $this->compileDir = WCF_DIR . 'acp/templates/compiled/';
    }

    /**
     * Deletes all compiled acp templates.
     *
     * @param string $compileDir
     */
    public static function deleteCompiledACPTemplates($compileDir = '')
    {
        if (empty($compileDir)) {
            $compileDir = WCF_DIR . 'acp/templates/compiled/';
        }

        self::deleteCompiledTemplates($compileDir);
    }

    /**
     * @inheritDoc
     */
    public function getCompiledFilename($templateName, $application)
    {
        $abbreviation = 'wcf';
        if (PACKAGE_ID) {
            $abbreviation = ApplicationHandler::getInstance()->getActiveApplication()->getAbbreviation();
        }

        return $this->getCompileDir($templateName) . '_' . $abbreviation . '_' . $this->languageID . '_' . $templateName . '.php';
    }

    /**
     * This method always throws, because changing the template group is not supported.
     *
     * @param int $templateGroupID
     * @throws  \BadMethodCallException
     */
    public function setTemplateGroupID($templateGroupID)
    {
        throw new \BadMethodCallException("You may not change the template group of the acp template engine");
    }

    /**
     * @inheritDoc
     */
    public function getTemplateListenerCode($templateName, $eventName)
    {
        // skip template listeners within WCFSetup
        if (!PACKAGE_ID) {
            return '';
        }

        return parent::getTemplateListenerCode($templateName, $eventName);
    }
}
