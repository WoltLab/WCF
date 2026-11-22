<?php

namespace wcf\event\paid\subscription;

use wcf\data\paid\subscription\PaidSubscription;
use wcf\event\IPsr14Event;

/**
 * Indicates that a paid subscription is about to be deleted.
 * Fired before any data is removed, allowing the cleanup of linked data.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PaidSubscriptionDeleting implements IPsr14Event
{
    public function __construct(
        public readonly PaidSubscription $subscription,
    ) {}
}
