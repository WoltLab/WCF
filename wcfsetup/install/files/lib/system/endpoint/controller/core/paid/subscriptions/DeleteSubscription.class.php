<?php

namespace wcf\system\endpoint\controller\core\paid\subscriptions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\WCF;

/**
 * Deletes the paid subscription with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/paid/subscriptions/{id:\d+}')]
final class DeleteSubscription implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $subscription = Helper::fetchObjectFromRequestParameter($variables['id'], PaidSubscription::class);

        $this->assertSubscriptionCanBeDeleted();

        (new PaidSubscriptionAction([$subscription], 'delete'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertSubscriptionCanBeDeleted(): void
    {
        WCF::getSession()->checkPermissions(['admin.paidSubscription.canManageSubscription']);
    }
}
