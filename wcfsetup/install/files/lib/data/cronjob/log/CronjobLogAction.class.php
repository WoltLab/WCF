<?php

namespace wcf\data\cronjob\log;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes cronjob log-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `CronjobLogBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<CronjobLog, CronjobLogEditor>
 */
class CronjobLogAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = CronjobLogEditor::class;
}
