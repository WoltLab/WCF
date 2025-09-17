<?php

namespace wcf\system\endpoint\controller\core\bbcodes;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Deletes the bbcode with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/bbcodes/{id:\d+}')]
final class DeleteBBCode implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $bbCode = Helper::fetchObjectFromRequestParameter($variables['id'], BBCode::class);

        $this->assertBBCodeCanBeDeleted($bbCode);

        (new BBCodeAction([$bbCode], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertBBCodeCanBeDeleted(BBCode $bbcode): void
    {
        if (!WCF::getSession()->getPermission("admin.content.bbcode.canManageBBCode")) {
            throw new PermissionDeniedException();
        }

        if (!$bbcode->canDelete()) {
            throw new PermissionDeniedException();
        }
    }
}
