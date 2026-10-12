<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Retrieves the HTML code for the detailed activity point list of the user with the given ID.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/users/{id:\d+}/activity-points')]
final class GetUserActivityPointList implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $user = Helper::fetchObjectFromRequestParameter($variables['id'], User::class);

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($user->userID);
        if (
            $userProfile->userID !== WCF::getUser()->userID
            && !WCF::getSession()->hasPermission('user.profile.canViewUserProfile')
        ) {
            throw new PermissionDeniedException();
        }

        if ($userProfile->isProtected()) {
            throw new PermissionDeniedException();
        }

        return new JsonResponse([
            'template' => WCF::getTPL()->render('wcf', 'detailedActivityPointList', [
                'entries' => $this->getEntries($userProfile),
                'user' => $userProfile,
            ]),
        ]);
    }

    /**
     * @return list<array{activityPoints: int, items: int, objectType: ObjectType}>
     */
    private function getEntries(UserProfile $user): array
    {
        $objectTypes = [];
        foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent') as $objectType) {
            $objectTypes[$objectType->objectTypeID] = $objectType;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('userID = ?', [$user->userID]);
        $conditionBuilder->add('objectTypeID IN (?)', [\array_keys($objectTypes)]);

        $sql = "SELECT  objectTypeID, activityPoints, items
                FROM    wcf1_user_activity_point
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $entries = [];
        while ($row = $statement->fetchArray()) {
            $entries[] = [
                'activityPoints' => $row['activityPoints'],
                'items' => $row['items'],
                'objectType' => $objectTypes[$row['objectTypeID']],
            ];
        }

        return $entries;
    }
}
