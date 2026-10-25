<?php

namespace wcf\command\package\installation\queue;

use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;

/**
 * Cancels a package installation that has not been started yet.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class CancelPackageInstallation
{
    public function __construct(private readonly PackageInstallationQueue $queue) {}

    public function __invoke(): void
    {
        @\unlink($this->queue->archive);

        new PackageInstallationQueueEditor($this->queue)->delete();
    }
}
