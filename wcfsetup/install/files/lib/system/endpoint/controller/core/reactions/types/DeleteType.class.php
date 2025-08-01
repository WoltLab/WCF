<?php

namespace wcf\system\endpoint\controller\core\reactions\types;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\reaction\type\ReactionType;
use wcf\data\reaction\type\ReactionTypeAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the reaction type with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[DeleteRequest("/core/reactions/types/{id:\d+}")]
final class DeleteType implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertReactionTypeCanBeDeleted();

        $reactionType = Helper::fetchObjectFromRequestParameter($variables['id'], ReactionType::class);

        (new ReactionTypeAction([$reactionType], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertReactionTypeCanBeDeleted(): void
    {
        if (!\MODULE_LIKE) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(["admin.content.reaction.canManageReactionType"]);
    }
}
