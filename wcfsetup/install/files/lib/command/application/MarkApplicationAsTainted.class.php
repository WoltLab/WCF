<?php

namespace wcf\command\application;

use wcf\data\application\Application;
use wcf\data\application\ApplicationEditor;
use wcf\system\cache\eager\ApplicationCache;

/**
 * Marking an application as tainted, prevents it from loading.
 * This should be called during the uninstallation.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class MarkApplicationAsTainted
{
    public function __construct(public readonly Application $application)
    {
    }

    public function __invoke(): void
    {
        $applicationEditor = new ApplicationEditor($this->application);
        $applicationEditor->update(['isTainted' => 1]);

        (new ApplicationCache())->rebuild();
    }
}
