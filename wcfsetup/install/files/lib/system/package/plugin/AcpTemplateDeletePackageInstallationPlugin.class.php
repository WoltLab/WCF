<?php

namespace wcf\system\package\plugin;

use wcf\data\application\Application;

/**
 * Deletes templates installed with the `acpTemplate` package installation plugin.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
 * @since   5.5
 */
final class AcpTemplateDeletePackageInstallationPlugin extends AbstractTemplateDeletePackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    protected function getLogTableName(): string
    {
        return 'wcf1_acp_template';
    }

    /**
     * @inheritDoc
     */
    protected function getFilePath(string $filename, string $application): string
    {
        return \sprintf(
            '%s/acp/templates/%s.tpl',
            Application::getDirectory($application),
            $filename
        );
    }
}
