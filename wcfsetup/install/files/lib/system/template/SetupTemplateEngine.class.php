<?php

namespace wcf\system\template;

/**
 * Loads and displays template during the setup process.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SetupTemplateEngine extends TemplateEngine
{
    #[\Override]
    protected function loadTemplateGroupCache()
    {
        // does nothing
    }

    #[\Override]
    public function getSourceFilename(string $templateName, string $application)
    {
        return $this->compileDir . 'setup/template/' . $templateName . '.tpl';
    }

    #[\Override]
    public function getCompiledFilename(string $templateName, string $application)
    {
        return $this->compileDir . 'setup/template/compiled/' . $this->languageID . '_' . $templateName . '.php';
    }

    #[\Override]
    public function getMetaDataFilename(string $templateName)
    {
        return $this->compileDir . 'setup/template/compiled/' . $this->languageID . '_' . $templateName . '.meta.php';
    }
}
