<?php

namespace wcf\system\interaction\bulk;

use wcf\system\interaction\InteractionConfirmationType;

/**
 * Represents a bulk delete interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class BulkDeleteInteraction extends BulkRpcInteraction
{
    public function __construct(
        string $endpoint,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct(
            'delete',
            $endpoint,
            'wcf.global.button.delete',
            InteractionConfirmationType::Delete,
            '',
            $isAvailableCallback
        );
    }
}
