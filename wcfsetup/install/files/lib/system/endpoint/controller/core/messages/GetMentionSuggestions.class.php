<?php

namespace wcf\system\endpoint\controller\core\messages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\event\message\mention\MentionSuggestionProviderCollecting;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\message\mention\IMentionSuggestionProvider;
use wcf\system\WCF;

/**
 * Retrieves the list of users and groups that can be mentioned.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
#[GetRequest('/core/messages/mention-suggestions')]
final class GetMentionSuggestions implements IController
{
    private const MAX_USERS = 10;

    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetMentionSuggestionsParameters::class);
        $provider = $this->getProvider($parameters->objectType);
        if (\mb_strlen($parameters->query) < 3 && $provider === null) {
            throw new UserInputException('query', 'tooShort');
        }

        $query = \mb_strtolower($parameters->query);
        $matches = [];

        $contextUsers = [];
        if ($provider !== null && $parameters->objectID > 0) {
            $contextUsers = $this->getContextUsers($provider, $parameters->objectID, $query);
        }

        foreach ($contextUsers as $userProfile) {
            $matches[] = $this->getUserMatch($userProfile);
        }

        // Short queries are only supported for suggestions based on the context.
        if (\mb_strlen($query) >= 3) {
            foreach ($this->getGroups($query) as $userGroup) {
                $matches[] = [
                    'name' => $userGroup->getName(),
                    'groupID' => $userGroup->groupID,
                    'type' => 'group',
                ];
            }

            $limit = self::MAX_USERS - \count($contextUsers);
            if ($limit > 0) {
                $excludedUserIDs = \array_map(
                    static fn(UserProfile $userProfile) => $userProfile->userID,
                    $contextUsers
                );

                foreach ($this->getUsers($query, $limit, $excludedUserIDs) as $userProfile) {
                    $matches[] = $this->getUserMatch($userProfile);
                }
            }
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

    private function getProvider(string $objectType): ?IMentionSuggestionProvider
    {
        if ($objectType === '') {
            return null;
        }

        $event = new MentionSuggestionProviderCollecting();
        EventHandler::getInstance()->fire($event);

        return $event->getProviders()[$objectType] ?? null;
    }

    /**
     * @return list<UserProfile>
     */
    private function getContextUsers(IMentionSuggestionProvider $provider, int $objectID, string $query): array
    {
        $userIDs = $provider->getUserIDs($objectID, $query, self::MAX_USERS);
        if ($userIDs === []) {
            return [];
        }

        return \array_values(\array_filter(
            UserProfileRuntimeCache::getInstance()->getObjects($userIDs),
            static fn(?UserProfile $userProfile) => $userProfile !== null
        ));
    }

    /**
     * @return array{avatarTag: string, username: string, userID: int, type: 'user'}
     */
    private function getUserMatch(UserProfile $userProfile): array
    {
        return [
            'avatarTag' => $userProfile->getAvatar()->getImageTag(16),
            'username' => $userProfile->getUsername(),
            'userID' => $userProfile->getObjectID(),
            'type' => 'user',
        ];
    }

    /**
     * @param list<int> $excludedUserIDs
     * @return list<UserProfile>
     */
    private function getUsers(string $query, int $limit, array $excludedUserIDs): array
    {
        $userProfileList = new UserProfileList();
        $userProfileList->getConditionBuilder()->add("username LIKE ?", [WCF::getDB()->escapeLikeValue($query) . '%']);
        if ($excludedUserIDs !== []) {
            $userProfileList->getConditionBuilder()->add("user_table.userID NOT IN (?)", [$excludedUserIDs]);
        }

        $userProfileList->sqlLimit = $limit;
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
            static fn(UserGroup $a, UserGroup $b) => $collator->compare($a->getName(), $b->getName())
        );

        return $userGroups;
    }
}

/** @internal */
final class GetMentionSuggestionsParameters
{
    public function __construct(
        public readonly string $query = '',

        /**
         * Object type of the context used to prioritize relevant users.
         */
        public readonly string $objectType = '',

        public readonly int $objectID = 0,
    ) {}
}
