<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\User;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\user\command\Follow;
use wcf\system\user\command\Unfollow;
use wcf\system\WCF;

/**
 * Handles user follows.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class UserFollowAction implements RequestHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    id: positive-int
                }
                EOT
        );

        $this->assertUserIsLoggedIn();

        $user = new User($parameters['id']);
        $this->assertTargetCanBeFollowed($user);

        if ($request->getMethod() === 'GET') {
            return new TextResponse('Unsupported', 400);
        } elseif ($request->getMethod() === 'POST') {
            $bodyParameters = Helper::mapRequestBody(
                $request->getParsedBody(),
                <<<'EOT'
                    array {
                        action: "follow" | "unfollow"
                    }
                    EOT
            );

            if ($bodyParameters['action'] === 'follow') {
                $this->assertUserIsNotIgnored($user);

                $command = new Follow(WCF::getUser(), $user);
                $command();
            } else {
                $command = new Unfollow(WCF::getUser(), $user);
                $command();
            }

            return new EmptyResponse();
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function assertUserIsLoggedIn(): void
    {
        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    private function assertUserIsNotIgnored(User $target): void
    {
        $sql = "SELECT  ignoreID
                FROM    wcf1_user_ignore
                WHERE   userID = ?
                    AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $target->userID,
            WCF::getUser()->userID,
        ]);

        $ignoreID = $statement->fetchSingleColumn();
        if ($ignoreID !== false) {
            throw new PermissionDeniedException();
        }
    }

    private function assertTargetCanBeFollowed(User $target): void
    {
        if (!$target->userID) {
            throw new IllegalLinkException();
        }

        if ($target->userID === WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }
    }
}
