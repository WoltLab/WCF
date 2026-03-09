<?php

namespace wcf\system\endpoint\controller\core\users\reactions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\like\ViewableLikeList;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the rendering of the list of reactions for a user profile.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/users/{id:\d+}/reactions/render')]
final class RenderUserReactions implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!MODULE_LIKE) {
            throw new IllegalLinkException();
        }

        $user = UserProfileRuntimeCache::getInstance()->getObject(\intval($variables['id']));
        if ($user === null) {
            throw new IllegalLinkException();
        }

        $parameters = Helper::mapApiParameters($request, RenderUserReactionsParameters::class);

        $likeList = new ViewableLikeList();
        if ($parameters->lastLikeTime) {
            $likeList->getConditionBuilder()->add("like_table.time < ?", [$parameters->lastLikeTime]);
        }
        if ($parameters->targetType === 'received') {
            $likeList->getConditionBuilder()->add("like_table.objectUserID = ?", [$user->userID]);
        } else {
            $likeList->getConditionBuilder()->add("like_table.userID = ?", [$user->userID]);
        }
        if ($parameters->reactionTypeID) {
            $likeList->getConditionBuilder()->add(
                "like_table.reactionTypeID = ?",
                [$parameters->reactionTypeID]
            );
        }
        $likeList->readObjects();

        if ($likeList->getObjects() === []) {
            return new JsonResponse([]);
        }

        return new JsonResponse([
            'lastLikeTime' => $likeList->getLastLikeTime(),
            'template' => WCF::getTPL()->render('wcf', 'userProfileLikeItem', [
                'likeList' => $likeList,
            ]),
        ]);
    }
}

/** @internal */
final class RenderUserReactionsParameters
{
    public function __construct(
        public readonly string $targetType = 'received',
        public readonly int $lastLikeTime = 0,
        public readonly int $reactionTypeID = 0,
    ) {}
}
