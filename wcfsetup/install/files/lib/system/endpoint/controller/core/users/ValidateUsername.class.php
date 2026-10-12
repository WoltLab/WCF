<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\endpoint\AvailableDuringForceLogin;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\util\UserRegistrationUtil;

/**
 * Validates the given username for the registration process.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/validate-username')]
#[AvailableDuringForceLogin]
final class ValidateUsername implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, ValidateUsernameParameters::class);

        if (!User::getUserByUsername($parameters->username)->isNil()) {
            return new JsonResponse([
                'ok' => false,
                'error' => 'notUnique',
            ]);
        }

        if (!UserRegistrationUtil::isValidUsername($parameters->username)) {
            return new JsonResponse([
                'ok' => false,
                'error' => 'invalid',
            ]);
        }

        return new JsonResponse([
            'ok' => true,
        ]);
    }
}

/** @internal */
final class ValidateUsernameParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $username,
    ) {}
}
