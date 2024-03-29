<?php

namespace wcf\data\acl\option\category;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit acl option categories.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static ACLOptionCategory   create(array $parameters = [])
 * @method      ACLOptionCategory   getDecoratedObject()
 * @mixin       ACLOptionCategory
 */
class ACLOptionCategoryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    public static $baseClass = ACLOptionCategory::class;
}
