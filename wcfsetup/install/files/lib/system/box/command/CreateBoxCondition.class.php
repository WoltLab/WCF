<?php

namespace wcf\system\box\command;

use wcf\system\WCF;

/**
 * Creates a new condition for an existing box.
 *
 * Note: The primary use of this command is to be used during package installation.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\box\CreateBoxCondition` instead.
 */
final class CreateBoxCondition
{
    /**
     * @param mixed[] $conditionData
     */
    public function __construct(
        private readonly string $boxIdentifier,
        private readonly string $conditionDefinition,
        private readonly string $conditionObjectType,
        private readonly array $conditionData
    ) {}

    /**
     * @throws \InvalidArgumentException
     */
    public function __invoke(): void
    {
        (new \wcf\command\box\CreateBoxCondition(
            $this->boxIdentifier,
            $this->conditionDefinition,
            $this->conditionObjectType,
            $this->conditionData
        ))();
    }
}
