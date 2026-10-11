<?php

namespace wcf\acp\action;

use CuyZ\Valinor\Mapper\MappingError;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\data\user\UserList;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\MultipleSelectionFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Adds one or more users to the selected user groups.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserAssignToGroupAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $users = $this->getUsers($request);

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $groupIDs = \array_map(
                static fn($groupID) => (int)$groupID,
                $form->getData()['data']['groupIDs']
            );

            (new UserAction(\array_values($users), 'addToGroups', [
                'groups' => $groupIDs,
                'deleteOldGroups' => false,
                'addDefaultGroups' => false,
            ]))->executeAction();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * @return array<int, User>
     */
    private function getUsers(ServerRequestInterface $request): array
    {
        try {
            $parameters = Helper::mapQueryParameters(
                $request->getQueryParams(),
                <<<'EOT'
                    array {
                        ids: non-empty-list<positive-int>
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $userIDs = \array_unique($parameters['ids']);

        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();
        $users = $userList->getObjects();
        if (\count($users) !== \count($userIDs)) {
            throw new IllegalLinkException();
        }

        foreach ($users as $user) {
            if (!$user->canEdit()) {
                throw new PermissionDeniedException();
            }
        }

        return $users;
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.acp.user.assignToGroup')
        );
        $form->appendChildren([
            MultipleSelectionFormField::create('groupIDs')
                ->label('wcf.acp.user.groups')
                ->options(
                    UserGroup::getSortedAccessibleGroups(
                        [],
                        [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]
                    ),
                    false,
                    false
                )
                ->required(),
        ]);
        $form->build();

        return $form;
    }
}
