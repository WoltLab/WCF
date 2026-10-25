<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Psr\Http\Message\ServerRequestInterface;
use wcf\acp\page\DevtoolsProjectListPage;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\http\Helper;
use wcf\system\devtools\pip\DevtoolsPackageInstallationDispatcher;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\request\LinkHandler;

/**
 * Handles an AJAX-based package installation of devtools projects.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class DevtoolsInstallPackageAction extends InstallPackageAction
{
    #[\Override]
    protected function createDispatcher(
        ServerRequestInterface $request,
        PackageInstallationQueue $queue
    ): PackageInstallationDispatcher {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        projectID: positive-int
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $project = new DevtoolsProject($parameters['projectID']);
        if ($project->isNil()) {
            throw new IllegalLinkException();
        }

        return new DevtoolsPackageInstallationDispatcher($project, $queue);
    }

    #[\Override]
    protected function getRedirectLink(string $redirectLocation): string
    {
        return LinkHandler::getInstance()->getControllerLink(DevtoolsProjectListPage::class);
    }
}
