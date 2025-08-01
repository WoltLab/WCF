<?php

namespace wcf\system\endpoint\controller\core\paid\subscriptions\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\paid\subscription\user\PaidSubscriptionUser;
use wcf\data\paid\subscription\user\PaidSubscriptionUserAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Deletes the paid subscription user with the given ID.
 *
 * API endpoint for the deletion of paid subscriptions for users.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/paid/subscriptions/users/{id:\d+}')]
final class DeleteSubscriptionUser implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertSubscriptionUserCanDeleted();

        $user = Helper::fetchObjectFromRequestParameter($variables['id'], PaidSubscriptionUser::class);

        (new PaidSubscriptionUserAction([$user], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertSubscriptionUserCanDeleted(): void
    {
        if (!\MODULE_PAID_SUBSCRIPTION) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.paidSubscription.canManageSubscription']);
    }
}
