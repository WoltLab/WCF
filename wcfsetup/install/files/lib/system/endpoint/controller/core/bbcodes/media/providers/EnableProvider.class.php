<?php

namespace wcf\system\endpoint\controller\core\bbcodes\media\providers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\bbcode\media\provider\BBCodeMediaProvider;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\WCF;

/**
 * Enables the media provider with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/bbcodes/media/providers/{id:\d+}/enable')]
final class EnableProvider implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $provider = Helper::fetchObjectFromRequestParameter($variables['id'], BBCodeMediaProvider::class);

        $this->assertMediaProviderCanBeEnabled();

        (new \wcf\command\bbcode\media\provider\EnableBBCodeMediaProvider($provider))();

        return new JsonResponse([]);
    }

    private function assertMediaProviderCanBeEnabled(): void
    {
        WCF::getSession()->checkPermissions(['admin.content.bbcode.canManageBBCode']);
    }
}
