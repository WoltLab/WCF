<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\action\IAction;
use wcf\data\user\User;
use wcf\http\attribute\AllowAccessToken;
use wcf\http\error\NotFoundHandler;
use wcf\page\IPage;
use wcf\system\request\RequestHandler;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Handles a given access-token, that allow the user to be authed for the current request.
 * A missing token will be ignored, an invalid token results in a throw of a IllegalLinkException.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class HandleAccessToken implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->handleAccessToken($request->getQueryParams()['at'] ?? '')) {
            return (new NotFoundHandler())->handle($request);
        }

        return $handler->handle($request);
    }

    private function handleAccessToken(string $accessToken): bool
    {
        if ($accessToken === '') {
            return true;
        }

        $activeRequest = RequestHandler::getInstance()->getActiveRequest();
        if ($activeRequest === null) {
            return true;
        }

        $reflectionClass = new \ReflectionClass($activeRequest->getClassName());
        if (!$this->hasAttribute($reflectionClass)) {
            return true;
        }

        return $this->checkAccessToken($accessToken);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function hasAttribute(\ReflectionClass $class): bool
    {
        if ($class->getAttributes(AllowAccessToken::class) !== []) {
            return true;
        }

        $parentClass = $class->getParentClass();
        if ($parentClass === false) {
            return false;
        }

        return $this->hasAttribute($parentClass);
    }

    private function checkAccessToken(string $accessToken): bool
    {
        if (!\preg_match('~^(?P<userID>\d{1,10})-(?P<token>[a-f0-9]{40})$~', $accessToken, $matches)) {
            return false;
        }

        $userID = (int)$matches['userID'];
        $token = $matches['token'];

        if (!WCF::getUser()->isGuest()) {
            if ($userID === WCF::getUser()->userID && \hash_equals(WCF::getUser()->accessToken, $token)) {
                // Everything is fine, but the user is already logged in.
                return true;
            }
        } else {
            $user = new User($userID);
            if (
                !$user->isGuest() && $user->accessToken !== '' && \hash_equals(
                    $user->accessToken,
                    $token
                )
            ) {
                // Token is valid so we log in the user for the current request.
                SessionHandler::getInstance()->changeUser($user, true);
                return true;
            }
        }

        return false;
    }
}
