<?php

namespace wcf\system\interaction;

/**
 * Represents a delete interaction.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class DeleteInteraction extends RpcInteraction
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
