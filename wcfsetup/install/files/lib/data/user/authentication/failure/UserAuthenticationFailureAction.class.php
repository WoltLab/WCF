<?php

namespace wcf\data\user\authentication\failure;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes user authentication failure-related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<UserAuthenticationFailure, UserAuthenticationFailureEditor>
 * @deprecated 6.3 use the `LogUserAuthenticationFailure` command or `UserAuthenticationFailureBuilder` instead.
 */
class UserAuthenticationFailureAction extends AbstractDatabaseObjectAction
{
}
