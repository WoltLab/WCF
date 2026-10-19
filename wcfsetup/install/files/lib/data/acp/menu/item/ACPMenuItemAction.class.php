<?php

namespace wcf\data\acp\menu\item;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes ACP menu item-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<ACPMenuItem, ACPMenuItemEditor>
 * @deprecated 6.3
 */
class ACPMenuItemAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = ACPMenuItemEditor::class;
}
