<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\util\UserRegistrationUtil;

/**
 * Validates the given email for the registration process.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class EmailValidationAction implements RequestHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() === 'POST') {
            $bodyParameters = Helper::mapRequestBody(
                $request->getParsedBody(),
                <<<'EOT'
                    array {
                        email: string
                    }
                    EOT
            );

            $result = [
                'ok' => true,
            ];

            if (!UserRegistrationUtil::isValidEmail($bodyParameters['email'])) {
                $result = [
                    'ok' => false,
                    'error' => 'invalid',
                ];
            }

            if (User::getUserByEmail($bodyParameters['email'])->userID) {
                $result = [
                    'ok' => false,
                    'error' => 'notUnique',
                ];
            }

            return new JsonResponse($result);
        } else {
            throw new \LogicException('Unreachable');
        }
    }
}
