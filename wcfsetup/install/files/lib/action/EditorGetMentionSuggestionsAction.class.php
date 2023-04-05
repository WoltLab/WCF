<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\http\Helper;

/**
 * Suggests users that may be mentioned.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @since   6.0
 */
final class EditorGetMentionSuggestionsAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                    array {
                        query: string
                    }
                    EOT,
        );

        $users = $this->getUsers($parameters);

        // TODO: Groups

        return new JsonResponse(
            \array_map(
                static fn (UserProfile $userProfile) => [
                    'avatarTag' => $userProfile->getAvatar()->getImageTag(16),
                    'username' => $userProfile->getUsername(),
                    'userID' => $userProfile->getObjectID(),
                    'type' => 'user',
                ],
                $users
            ),
            200,
            [
                'cache-control' => [
                    'max-age=300',
                ],
            ]
        );
    }

    /**
     * @return list<UserProfile>
     */
    private function getUsers(array $parameters): array
    {
        $userProfileList = new UserProfileList();
        $userProfileList->getConditionBuilder()->add("username LIKE ?", [$parameters['query'] . '%']);

        $userProfileList->sqlLimit = 10;
        $userProfileList->readObjects();

        return \array_values($userProfileList->getObjects());
    }
}
