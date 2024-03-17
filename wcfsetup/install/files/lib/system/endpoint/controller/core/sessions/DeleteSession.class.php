<?php

namespace wcf\system\endpoint\controller\core\sessions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\UserInputException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

/**
 * Deletes one of the current user’s sessions, causing a device with that
 * session id to be logged out.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
#[DeleteRequest('/core/sessions/{id}')]
final class DeleteSession implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $sessionID = $variables['id'];

        if (!$this->isOwnSessionID($sessionID)) {
            throw new UserInputException('sessionID');
        }

        SessionHandler::getInstance()->deleteUserSession($sessionID);

        return new JsonResponse([]);
    }

    private function isOwnSessionID(string $sessionID): bool
    {
        foreach (SessionHandler::getInstance()->getUserSessions(WCF::getUser()) as $session) {
            if ($session->getSessionID() === $sessionID) {
                return true;
            }
        }

        return false;
    }
}
