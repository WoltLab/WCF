<?php

namespace wcf\data\paid\subscription\transaction\log;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit paid subscription transaction log entries.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static PaidSubscriptionTransactionLog      create(array $parameters = [])
 * @method      PaidSubscriptionTransactionLog      getDecoratedObject()
 * @mixin       PaidSubscriptionTransactionLog
 */
class PaidSubscriptionTransactionLogEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = PaidSubscriptionTransactionLog::class;
}
