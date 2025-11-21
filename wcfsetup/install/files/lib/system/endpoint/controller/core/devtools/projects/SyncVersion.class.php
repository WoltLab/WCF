<?php

namespace wcf\system\endpoint\controller\core\devtools\projects;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\Package;
use wcf\data\package\PackageEditor;
use wcf\http\Helper;
use wcf\system\cache\builder\PackageCacheBuilder;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Syncs the version of the installed package with the one provided by the
 * package.xml in the project.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/devtools/projects/{id:\d+}/sync-version')]
final class SyncVersion implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $project = Helper::fetchObjectFromRequestParameter($variables['id'], DevtoolsProject::class);

        $this->assertProjectCanBeManaged();

        $package = $project->getPackage();
        if ($package === null) {
            throw new UserInputException('projectID');
        }

        $this->syncVersion($project, $package);

        return new JsonResponse([]);
    }

    private function syncVersion(DevtoolsProject $project, Package $package): void
    {
        $projectVersion = $project->getPackageArchive()->getPackageInfo('version');
        if (Package::compareVersion($package->packageVersion, $projectVersion, '>=')) {
            return;
        }

        (new PackageEditor($package))->update([
            'packageVersion' => $projectVersion,
        ]);

        PackageCacheBuilder::getInstance()->reset();
    }

    private function assertProjectCanBeManaged(): void
    {
        if (!ENABLE_DEVELOPER_TOOLS || !WCF::getSession()->getPermission('admin.configuration.package.canInstallPackage')) {
            throw new PermissionDeniedException();
        }
    }
}
