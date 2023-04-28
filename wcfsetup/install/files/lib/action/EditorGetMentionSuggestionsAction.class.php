<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\http\Helper;
use wcf\system\WCF;

/**
 * Suggests users that may be mentioned.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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

        $query = \mb_strtolower($parameters['query']);
        $matches = [];

        foreach ($this->getGroups($query) as $userGroup) {
            $matches[] = [
                'name' => $userGroup->getName(),
                'groupID' => $userGroup->groupID,
                'type' => 'group',
            ];
        }

        foreach ($this->getUsers($query) as $userProfile) {
            $matches[] = [
                'avatarTag' => $userProfile->getAvatar()->getImageTag(16),
                'username' => $userProfile->getUsername(),
                'userID' => $userProfile->getObjectID(),
                'type' => 'user',
            ];
        }

        return new JsonResponse(
            $matches,
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
    private function getUsers(string $query): array
    {
        $userProfileList = new UserProfileList();
        $userProfileList->getConditionBuilder()->add("username LIKE ?", [$query . '%']);

        $userProfileList->sqlLimit = 10;
        $userProfileList->readObjects();

        return \array_values($userProfileList->getObjects());
    }

    /**
     * @return list<UserGroup>
     */
    private function getGroups(string $query): array
    {
        $userGroups = UserGroup::getMentionableGroups();
        if ($userGroups === []) {
            return [];
        }

        $userGroups = \array_filter($userGroups, static function (UserGroup $userGroup) use ($query) {
            return \str_starts_with(\mb_strtolower($userGroup->getName()), $query);
        });

        $c = new \Collator(WCF::getLanguage()->getLocale());
        \usort(
            $userGroups,
            static fn (UserGroup $a, UserGroup $b) => $c->compare($a->getName(), $b->getName())
        );

        return $userGroups;
    }
}
