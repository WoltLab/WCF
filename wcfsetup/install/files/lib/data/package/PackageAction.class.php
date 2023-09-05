<?php

namespace wcf\data\package;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  Package         create()
 * @method  PackageEditor[]     getObjects()
 * @method  PackageEditor       getSingleObject()
 */
class PackageAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PackageEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.configuration.package.canInstallPackage'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.configuration.package.canUninstallPackage'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.configuration.package.canUpdatePackage'];
}
