<?php

namespace wcf\system\interaction;

/**
 * Represents an interaction that links to an edit form controller.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class EditInteraction extends LinkInteraction
{
    public function __construct(
        string $controllerClass,
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct('edit', $controllerClass, 'wcf.global.button.edit', $isAvailableCallback);
    }
}
