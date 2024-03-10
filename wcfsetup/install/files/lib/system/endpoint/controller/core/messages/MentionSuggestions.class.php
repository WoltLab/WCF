<?php

namespace wcf\system\endpoint\controller\core\messages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfileList;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\Parameters;
use wcf\system\WCF;

final class MentionSuggestions implements IController
{
    #[GetRequest('/core/messages/mentionsuggestions')]
    public function mentionSuggestions(
        #[Parameters(
            <<<'EOT'
                array {
                    query: non-empty-string
                }
                EOT,
        )]
        array $parameters
    ): ResponseInterface {
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

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \usort(
            $userGroups,
            static fn (UserGroup $a, UserGroup $b) => $collator->compare($a->getName(), $b->getName())
        );

        return $userGroups;
    }
}
