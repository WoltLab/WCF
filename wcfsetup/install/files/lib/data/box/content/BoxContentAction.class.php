<?php

namespace wcf\data\box\content;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes box content related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  BoxContentEditor[]  getObjects()
 * @method  BoxContentEditor    getSingleObject()
 */
class BoxContentAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = BoxContentEditor::class;
}
