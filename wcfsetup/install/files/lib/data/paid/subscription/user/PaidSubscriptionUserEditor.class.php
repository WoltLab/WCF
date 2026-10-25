<?php

namespace wcf\data\paid\subscription\user;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit paid subscription users.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.3 Use `PaidSubscriptionUserBuilder` instead.
 *
 * @mixin       PaidSubscriptionUser
 * @extends DatabaseObjectEditor<PaidSubscriptionUser>
 */
class PaidSubscriptionUserEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PaidSubscriptionUser::class;
}
