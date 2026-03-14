<?php

namespace wcf\data\like;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes like-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  since 5.2, use \wcf\data\reaction\ReactionAction instead
 *
 * @extends AbstractDatabaseObjectAction<Like, LikeEditor>
 */
class LikeAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = LikeEditor::class;
}
