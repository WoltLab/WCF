<?php

namespace wcf\data\user\profile\visitor;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit profile visitors.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 Use `UserProfileVisitorBuilder` instead.
 *
 * @mixin       UserProfileVisitor
 * @extends DatabaseObjectEditor<UserProfileVisitor>
 */
class UserProfileVisitorEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserProfileVisitor::class;
}
