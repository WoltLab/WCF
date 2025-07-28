<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
abstract class UserManagementAction implements RequestHandlerInterface
{
    #[\Override]
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

        $user = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        $this->assertUserCanBeManaged($user);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];

            $this->performAction($user, $data);

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    protected function assertUserCanBeManaged(?UserProfile $userProfile): void
    {
        if (!$userProfile) {
            throw new IllegalLinkException();
        }

        if ($userProfile->userID === WCF::getUser()->userID) {
            throw new IllegalLinkException();
        }

        if (!UserGroup::isAccessibleGroup($userProfile->getGroupIDs())) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    abstract protected function performAction(UserProfile $userProfile, array $data): void;

    abstract protected function getForm(): Psr15DialogForm;
}
