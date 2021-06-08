<?php

namespace wcf\system\package\plugin;

/**
 * Deletes files installed with the `file` package installation plugin.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Package\Plugin
 * @since   5.5
 */
final class FileDeletePackageInstallationPlugin extends AbstractFileDeletePackageInstallationPlugin
{
    /**
     * @inheritDoc
     */
    public $tagName = 'file';

    /**
     * @inheritDoc
     */
    protected function getLogTableName(): string
    {
        return 'wcf1_package_installation_file_log';
    }

    /**
     * @inheritDoc
     */
    protected function getFilenameTableColumn(): string
    {
        return 'filename';
    }
}
