<?php

namespace wcf\data\paid\subscription;

use wcf\system\l10n\L10nStorage;

/**
 * List of paid subscriptions with localized title and description values.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class L10nPaidSubscriptionList extends PaidSubscriptionList
{
    /**
     * @inheritDoc
     */
    public $className = PaidSubscription::class;

    public function __construct()
    {
        parent::__construct();

        $storage = new L10nStorage(PaidSubscription::getL10nDefinition());

        $this->sqlSelects .= ($this->sqlSelects !== '' ? ', ' : '')
            . $storage->getSubSelect('title', $this->getDatabaseTableAlias())
            . ' AS title, '
            . $storage->getSubSelect('description', $this->getDatabaseTableAlias())
            . ' AS description';
    }
}
