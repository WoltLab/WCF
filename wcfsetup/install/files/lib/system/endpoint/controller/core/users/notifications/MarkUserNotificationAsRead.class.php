<?php

namespace wcf\system\endpoint\controller\core\users\notifications;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\notification\UserNotification;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Marks a user notification as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[PostRequest('/core/users/notifications/{id:\d+}/mark-as-read')]
final class MarkUserNotificationAsRead implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $notification = Helper::fetchObjectFromRequestParameter($variables['id'], UserNotification::class);

        $this->assertNotificationCanBeMarkedAsRead($notification);

        UserNotificationHandler::getInstance()->markAsConfirmedByIDs([$notification->notificationID]);

        return new JsonResponse([
            'unreadNotifications' => UserNotificationHandler::getInstance()->getNotificationCount(true),
        ]);
    }

    private function assertNotificationCanBeMarkedAsRead(UserNotification $notification): void
    {
        if ($notification->userID !== WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }
}
