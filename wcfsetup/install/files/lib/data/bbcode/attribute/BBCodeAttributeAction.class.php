<?php

namespace wcf\data\bbcode\attribute;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes bbcode attribute-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  BBCodeAttribute         create()
 * @method  BBCodeAttributeEditor[]     getObjects()
 * @method  BBCodeAttributeEditor       getSingleObject()
 */
class BBCodeAttributeAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = BBCodeAttributeEditor::class;
}
