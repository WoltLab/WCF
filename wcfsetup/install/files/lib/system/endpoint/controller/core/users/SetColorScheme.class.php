<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Sets the preferred color scheme of the active user.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/color-scheme')]
final class SetColorScheme implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        // Guests keep their choice in the browser.
        if (WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapApiParameters($request, SetColorSchemeParameters::class);

        new \wcf\command\user\SetColorScheme(WCF::getUser(), $parameters->colorScheme)();

        return new JsonResponse([]);
    }
}

/** @internal */
final class SetColorSchemeParameters
{
    public function __construct(
        /** @var 'dark'|'light'|'system' */
        public readonly string $colorScheme,
    ) {}
}
