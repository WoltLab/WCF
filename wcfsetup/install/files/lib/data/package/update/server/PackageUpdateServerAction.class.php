<?php

namespace wcf\data\package\update\server;

use wcf\command\package\update\server\DisablePackageUpdateServer;
use wcf\command\package\update\server\EnablePackageUpdateServer;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\exception\PermissionDeniedException;

/**
 * Executes package update server-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PackageUpdateServer, PackageUpdateServerEditor>
 */
class PackageUpdateServerAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = PackageUpdateServerEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.configuration.package.canEditServer'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.configuration.package.canEditServer'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.configuration.package.canEditServer'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'toggle', 'update'];

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        /** @var PackageUpdateServer $updateServer */
        foreach ($this->getObjects() as $updateServer) {
            if (!$updateServer->canDelete()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     *
     * @deprecated 6.3
     */
    public function validateToggle()
    {
        $this->validateUpdate();

        /** @var PackageUpdateServer $updateServer */
        foreach ($this->getObjects() as $updateServer) {
            if (!$updateServer->canDisable()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @deprecated 6.3 use the `EnablePackageUpdateServer` or `DisablePackageUpdateServer` commands instead.
     */
    public function toggle()
    {
        foreach ($this->objects as $editor) {
            if ($editor->isDisabled) {
                (new EnablePackageUpdateServer($editor->getDecoratedObject()))();
            } else {
                (new DisablePackageUpdateServer($editor->getDecoratedObject()))();
            }
        }
    }
}
