<?php

namespace wcf\data\application;

use wcf\command\application\MarkApplicationAsTainted;
use wcf\command\application\RebuildApplicationsCookieDomain;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes application-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Application, ApplicationEditor>
 */
class ApplicationAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ApplicationEditor::class;

    /**
     * Assigns a list of applications to a group and computes cookie domain.
     *
     * @return void
     *
     * @deprecated 6.3 use `RebuildApplicationsCookieDomain`
     */
    public function rebuild()
    {
        (new RebuildApplicationsCookieDomain())();
    }

    /**
     * Marks an application as tainted, prevents loading it during uninstallation.
     *
     * @return void
     *
     * @deprecated 6.3 use `MarkApplicationAsTainted`
     */
    public function markAsTainted()
    {
        (new MarkApplicationAsTainted($this->getSingleObject()->getDecoratedObject()))();
    }
}
