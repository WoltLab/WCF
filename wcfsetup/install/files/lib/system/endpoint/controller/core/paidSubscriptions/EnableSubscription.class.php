<?php

namespace wcf\system\endpoint\controller\core\paidSubscriptions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\paid\subscription\PaidSubscription;
use wcf\data\paid\subscription\PaidSubscriptionAction;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Enables the paid subscription with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/paid-subscriptions/{id:\d+}/enable')]
final class EnableSubscription implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $subscription = Helper::fetchObjectFromRequestParameter($variables['id'], PaidSubscription::class);

        $this->assertSubscriptionCanBeEnabled($subscription);

        (new PaidSubscriptionAction([$subscription], 'toggle'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertSubscriptionCanBeEnabled(PaidSubscription $subscription): void
    {
        WCF::getSession()->checkPermissions(['admin.paidSubscription.canManageSubscription']);

        if (!$subscription->isDisabled) {
            throw new PermissionDeniedException();
        }
    }
}
