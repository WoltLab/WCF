<?php

namespace wcf\data\blacklist\status;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes blacklist status-related actions.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `BlacklistStatusBuilder` instead.
 *
 * @extends AbstractDatabaseObjectAction<BlacklistStatus, BlacklistStatusEditor>
 * @since 5.2
 */
class BlacklistStatusAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = BlacklistStatusEditor::class;
}
