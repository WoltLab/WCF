<?php

namespace wcf\system\interaction\bulk;

use wcf\system\interaction\InteractionConfirmationType;

/**
 * Represents a bulk soft-delete interaction.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkSoftDeleteInteraction extends BulkRpcInteraction
{
    public function __construct(
        string $endpoint,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct(
            'soft-delete',
            $endpoint,
            'wcf.global.button.trash',
            InteractionConfirmationType::Custom,
            'wcf.dialog.confirmation.softDelete.indeterminate',
            $isAvailableCallback
        );
    }
}
