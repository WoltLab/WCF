<?php

namespace wcf\data\bbcode\attribute;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit bbcode attributes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `BBCodeAttributeBuilder` instead.
 *
 * @mixin       BBCodeAttribute
 * @extends DatabaseObjectEditor<BBCodeAttribute>
 */
class BBCodeAttributeEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = BBCodeAttribute::class;
}
