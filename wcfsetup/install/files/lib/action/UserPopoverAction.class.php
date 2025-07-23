<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\interaction\InteractionContextMenuComponent;
use wcf\system\interaction\InteractionContextMenuComponentConfiguration;
use wcf\system\interaction\user\UserCardInteractions;
use wcf\system\interaction\user\UserCardQuickInteractions;
use wcf\system\WCF;

/**
 * Provides the popover content for a user.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class UserPopoverAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id: positive-int
                }
                EOT,
        );

        $user = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        if (!$user) {
            return new EmptyResponse();
        }

        $quickInteractions = new UserCardQuickInteractions();
        $interactionContextMenuComponent = new InteractionContextMenuComponent(
            new UserCardInteractions(),
            new InteractionContextMenuComponentConfiguration(
                'userCard__button',
                'ellipsis',
                24
            )
        );

        return new HtmlResponse(
            WCF::getTPL()->render('wcf', 'userPopover', [
                'user' => $user,
                'quickInteractions' => $this->renderQuickInteractions($quickInteractions, $user),
                'contextMenuButton' => $interactionContextMenuComponent->renderButton($user),
                'interactionInitialization' => $this->renderInteractionInitialization(
                    $interactionContextMenuComponent,
                    $quickInteractions,
                    $user
                )
            ]),
        );
    }

    private function renderQuickInteractions(UserCardQuickInteractions $provider, UserProfile $user): string
    {
        $availableInteractions = \array_filter(
            $provider->getInteractions(),
            static fn($interaction) => $interaction->isAvailable($user)
        );

        return \implode("\n", \array_map(
            static fn($interaction) => $interaction->render($user),
            $availableInteractions
        ));
    }

    private function renderInteractionInitialization(
        InteractionContextMenuComponent $interactionContextMenuComponent,
        UserCardQuickInteractions $quickInteractions,
        UserProfile $user
    ): string {
        $code = $interactionContextMenuComponent->renderInitialization('userPopover_' . $user->userID);
        $code .= "\n";
        $code .= \implode("\n", \array_map(
            fn($interaction) => $interaction->renderInitialization('userPopover_' . $user->userID),
            $quickInteractions->getInteractions()
        ));

        return $code;
    }
}
