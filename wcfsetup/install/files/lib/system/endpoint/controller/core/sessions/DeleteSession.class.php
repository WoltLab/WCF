<?php

namespace wcf\system\endpoint\controller\core\sessions;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\IllegalLinkException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;

#[DeleteRequest('/core/sessions/{id}')]
final class DeleteSession implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $sessionID = $variables['id'];

        if (!$this->isOwnSessionID($sessionID)) {
            throw new IllegalLinkException();
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
