<?php

namespace wcf\system\box\command;

use wcf\system\WCF;

/**
 * Assigns pages to a certain box.
 *
 * Note: The primary use of this command is to be used during package installation.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\box\CreateBoxToPageAssignments` instead.
 */
final class CreateBoxToPageAssignments
{
    /**
     * @param string[] $pageIdentifiers
     */
    public function __construct(
        private readonly string $boxIdentifier,
        private readonly array $pageIdentifiers,
        private readonly bool $visible = true,
    ) {}

    /**
     * @throws \InvalidArgumentException
     */
    public function __invoke(): void
    {
        (new \wcf\command\box\CreateBoxToPageAssignments(
            $this->boxIdentifier,
            $this->pageIdentifiers,
            $this->visible
        ))();
    }
}
