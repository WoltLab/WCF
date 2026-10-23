<?php

namespace wcf\data\poll\option;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes poll option-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<PollOption, PollOptionEditor>
 * @deprecated 6.3 Use `PollBuilder` instead.
 */
class PollOptionAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = PollOptionEditor::class;
}
