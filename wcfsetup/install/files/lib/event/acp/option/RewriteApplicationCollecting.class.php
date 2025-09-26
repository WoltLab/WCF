<?php

namespace wcf\event\acp\option;

use wcf\event\IPsr14Event;

/**
 * Requests the collection additional applications for the rewrite configuration.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class RewriteApplicationCollecting implements IPsr14Event
{
    /**
     * @var array<string, string>
     */
    private array $applications = [];

    public function register(string $name, string $path): void
    {
        $this->applications[$name] = $path;
    }

    /**
     * @return array<string, string>
     */
    public function getApplications(): array
    {
        return $this->applications;
    }
}
