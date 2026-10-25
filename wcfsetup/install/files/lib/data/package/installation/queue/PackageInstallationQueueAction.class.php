<?php

namespace wcf\data\package\installation\queue;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package installation queue-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PackageInstallationQueue, PackageInstallationQueueEditor>
 */
class PackageInstallationQueueAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PackageInstallationQueueEditor::class;
}
