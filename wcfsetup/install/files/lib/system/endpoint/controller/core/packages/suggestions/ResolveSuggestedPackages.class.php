<?php

namespace wcf\system\endpoint\controller\core\packages\suggestions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\package\Package;
use wcf\data\package\update\server\PackageUpdateServer;
use wcf\data\package\update\ViewablePackageUpdate;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\package\InstallablePackageResolver;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\package\SuggestedPackageResolver;
use wcf\system\WCF;

/**
 * Refreshes the package database and returns the list of the suggested packages
 * that can be installed.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/packages/suggestions/resolve')]
final class ResolveSuggestedPackages implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canInstallPackage']);

        $parameters = Helper::mapApiParameters($request, ResolveSuggestedPackagesParameters::class);

        if (PackageUpdateServer::isUpgradeOverrideEnabled()) {
            return $this->getResponse([]);
        }

        $suggestions = \array_values(\array_filter(
            $parameters->suggestions,
            static fn(string $suggestion) => Package::isValidPackageName($suggestion)
        ));

        $this->refreshPackageDatabase();

        $resolver = new SuggestedPackageResolver();
        $suggestions = $resolver->removeInstalled(
            $resolver->merge($suggestions, $resolver->getStoredSuggestions($parameters->packageIDs))
        );

        $packages = \array_values(new InstallablePackageResolver()->resolveByIdentifiers($suggestions));
        \usort($packages, static function (ViewablePackageUpdate $a, ViewablePackageUpdate $b) {
            return \strnatcasecmp($a->getName(), $b->getName());
        });

        return $this->getResponse($packages);
    }

    /**
     * The package database is emptied after every installation, so the suggestions can
     * only be resolved after the package lists have been fetched again.
     */
    private function refreshPackageDatabase(): void
    {
        if (\ENABLE_BENCHMARK !== 0) {
            return;
        }

        try {
            PackageUpdateDispatcher::getInstance()->refreshPackageDatabase();
        } catch (\Throwable $e) {
            \wcf\functions\exception\logThrowable($e);
        }
    }

    /**
     * @param list<ViewablePackageUpdate> $packages
     */
    private function getResponse(array $packages): ResponseInterface
    {
        $template = '';
        if ($packages !== []) {
            $template = WCF::getTPL()->render('wcf', 'shared_packageInstallationSuggestedPackages', [
                'packages' => $packages,
            ]);
        }

        return new JsonResponse([
            'template' => $template,
        ]);
    }
}

/** @internal */
final class ResolveSuggestedPackagesParameters
{
    public function __construct(
        /** @var list<positive-int> */
        public readonly array $packageIDs,

        /** @var list<string> */
        public readonly array $suggestions,
    ) {}
}
