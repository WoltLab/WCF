<?php

namespace wcf\system\interaction\bulk;

use wcf\system\interaction\InteractionConfirmationType;

/**
 * Represents a bulk soft-delete interaction that allows the user to enter a reason.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkSoftDeleteWithReasonInteraction extends BulkRpcInteraction
{
    public function __construct(
        string $endpoint,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct(
            'soft-delete',
            $endpoint,
            'wcf.global.button.trash',
            InteractionConfirmationType::SoftDeleteWithReason,
            isAvailableCallback: $isAvailableCallback
        );
    }
}
