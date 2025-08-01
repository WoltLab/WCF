<?php

namespace wcf\system\endpoint\controller\core\tags;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\tag\Tag;
use wcf\data\tag\TagAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the tag with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/tags/{id:\d+}')]
final class DeleteTag implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $tag = Helper::fetchObjectFromRequestParameter($variables['id'], Tag::class);

        $this->assertTagCanBeDeleted();

        (new TagAction([$tag], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertTagCanBeDeleted(): void
    {
        if (!\MODULE_TAGGING) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.content.tag.canManageTag']);
    }
}
