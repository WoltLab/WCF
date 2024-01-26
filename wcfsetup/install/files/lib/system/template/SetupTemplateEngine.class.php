<?php

namespace wcf\system\template;

/**
 * Loads and displays template during the setup process.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
class SetupTemplateEngine extends TemplateEngine
{
    /**
     * @inheritDoc
     */
    protected function loadTemplateGroupCache()
    {
        // does nothing
    }

    /**
     * @inheritDoc
     */
    public function getSourceFilename($templateName, $application)
    {
        return $this->compileDir . 'setup/template/' . $templateName . '.tpl';
    }

    /**
     * @inheritDoc
     */
    public function getCompiledFilename($templateName, $application)
    {
        return $this->compileDir . 'setup/template/compiled/' . $this->languageID . '_' . $templateName . '.php';
    }

    /**
     * @inheritDoc
     */
    public function getMetaDataFilename($templateName)
    {
        return $this->compileDir . 'setup/template/compiled/' . $this->languageID . '_' . $templateName . '.meta.php';
    }
}
