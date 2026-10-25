<?php

namespace wcf\data\user\authentication\failure;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit user authentication failures.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin       UserAuthenticationFailure
 * @extends DatabaseObjectEditor<UserAuthenticationFailure>
 * @deprecated 6.3 use `UserAuthenticationFailureBuilder` instead.
 */
class UserAuthenticationFailureEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = UserAuthenticationFailure::class;
}
